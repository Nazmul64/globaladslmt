<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatRequestController extends Controller
{
    /* =========================================================
     | A. SEARCH USERS
     | Endpoint: GET /api/user-search?q={query}
     |=========================================================*/
    public function search(Request $request)
    {
        try {
            $search = trim($request->query('q', ''));

            if ($search === '') {
                return $this->successResponse([], 'No search query provided');
            }

            $authId = Auth::id();

            $users = User::where('id', '!=', $authId)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->select('id', 'name', 'email', 'photo')
                ->limit(20)
                ->get();

            if ($users->isEmpty()) {
                return $this->successResponse([], 'No users found');
            }

            $result = $users->map(function ($user) use ($authId) {
                return $this->formatUserWithStatus($user, $authId);
            });

            return $this->successResponse(
                $result,
                $result->count() . ' user(s) found'
            );

        } catch (Throwable $e) {
            Log::error('User search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Search failed', 500);
        }
    }

    /* =========================================================
     | B. SEND FRIEND REQUEST
     | Endpoint: POST /api/user/friend/request
     | Body: { "receiver_id": 123 }
     |=========================================================*/
    public function sendFriendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        try {
            $senderId   = Auth::id();
            $receiverId = (int) $request->receiver_id;

            // Check self-request
            if ($senderId === $receiverId) {
                return $this->errorResponse(
                    'You cannot send a friend request to yourself',
                    400
                );
            }

            // Check if request already exists (both directions)
            $exists = ChatRequest::where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($sq) use ($senderId, $receiverId) {
                    $sq->where('sender_id', $senderId)
                       ->where('receiver_id', $receiverId);
                })->orWhere(function ($sq) use ($senderId, $receiverId) {
                    $sq->where('sender_id', $receiverId)
                       ->where('receiver_id', $senderId);
                });
            })->first();

            if ($exists) {
                if ($exists->status === 'accepted') {
                    return $this->errorResponse('You are already friends', 409);
                }
                if ($exists->status === 'pending') {
                    $message = $exists->sender_id === $senderId
                        ? 'Friend request already sent'
                        : 'This user has already sent you a friend request';
                    return $this->errorResponse($message, 409);
                }
                if ($exists->status === 'rejected') {
                    // Allow re-sending after rejection
                    $exists->delete();
                }
            }

            // Create new friend request
            $friendRequest = ChatRequest::create([
                'sender_id'   => $senderId,
                'receiver_id' => $receiverId,
                'status'      => 'pending',
            ]);

            $sender = Auth::user();
            $senderName = $sender ? $sender->name : 'Someone';
            PushNotificationService::send(
                $receiverId,
                "নতুন ফ্রেন্ড রিকোয়েস্ট",
                "{$senderName} আপনাকে ফ্রেন্ড রিকোয়েস্ট পাঠিয়েছে",
                "friend_request",
                ['sender_id' => $senderId, 'request_id' => $friendRequest->id]
            );

            Log::info('Friend request sent', [
                'request_id' => $friendRequest->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $this->successResponse(
                [
                    'id' => $friendRequest->id,
                    'sender_id' => $friendRequest->sender_id,
                    'receiver_id' => $friendRequest->receiver_id,
                    'status' => $friendRequest->status,
                    'created_at' => $friendRequest->created_at->toIso8601String(),
                ],
                'Friend request sent successfully'
            );

        } catch (Throwable $e) {
            Log::error('Send friend request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'receiver_id' => $request->receiver_id ?? null
            ]);
            return $this->errorResponse('Failed to send friend request', 500);
        }
    }

    /* =========================================================
     | C. CANCEL FRIEND REQUEST
     | Endpoint: POST /api/cancel/friend/request
     | Body: { "receiver_id": 123 }
     |=========================================================*/
    public function cancelFriendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        try {
            $senderId = Auth::id();
            $receiverId = (int) $request->receiver_id;

            $deleted = ChatRequest::where([
                'sender_id'   => $senderId,
                'receiver_id' => $receiverId,
                'status'      => 'pending'
            ])->delete();

            if ($deleted) {
                Log::info('Friend request cancelled', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);
                return $this->successResponse(null, 'Friend request cancelled');
            } else {
                return $this->errorResponse('No pending request found', 404);
            }

        } catch (Throwable $e) {
            Log::error('Cancel friend request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to cancel request', 500);
        }
    }

    /* =========================================================
     | D. RECEIVED FRIEND REQUESTS (VIEW)
     | Endpoint: GET /api/user/friend/request/accept/view
     |=========================================================*/
    public function receivedRequests()
    {
        try {
            $authId = Auth::id();

            $requests = ChatRequest::where('receiver_id', $authId)
                ->where('status', 'pending')
                ->with('sender:id,name,email,photo')
                ->latest()
                ->get();

            if ($requests->isEmpty()) {
                return $this->successResponse([], 'No pending friend requests');
            }

            $formattedRequests = $requests->map(function ($r) {
                // Check if sender still exists
                if (!$r->sender) {
                    return null;
                }

                return [
                    'id' => $r->id,
                    'sender' => [
                        'id'    => $r->sender->id,
                        'name'  => $r->sender->name,
                        'email' => $r->sender->email,
                        'photo' => $this->resolvePhoto($r->sender->photo),
                    ],
                    'status' => $r->status,
                    'created_at' => $r->created_at->toIso8601String(),
                ];
            })->filter()->values(); // Remove null entries and reindex

            Log::info('Received requests loaded', [
                'user_id' => $authId,
                'count' => $formattedRequests->count()
            ]);

            return $this->successResponse(
                $formattedRequests,
                $formattedRequests->count() . ' pending request(s)'
            );

        } catch (Throwable $e) {
            Log::error('Received requests error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse('Failed to load friend requests', 500);
        }
    }

    /* =========================================================
     | E. ACCEPT FRIEND REQUEST
     | Endpoint: POST /api/user/friend/request/accept
     | Body: { "sender_id": 123 }
     |=========================================================*/
    public function acceptRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        DB::beginTransaction();

        try {
            $senderId = (int) $request->sender_id;
            $receiverId = Auth::id();

            $friendRequest = ChatRequest::where([
                'sender_id'   => $senderId,
                'receiver_id' => $receiverId,
                'status'      => 'pending'
            ])->lockForUpdate()->first();

            if (!$friendRequest) {
                DB::rollBack();
                return $this->errorResponse(
                    'Friend request not found or already processed',
                    404
                );
            }

            // Update status
            $friendRequest->update(['status' => 'accepted']);

            // Get sender info
            $sender = User::select('id', 'name', 'email', 'photo')
                ->find($senderId);

            if (!$sender) {
                DB::rollBack();
                return $this->errorResponse('Sender user not found', 404);
            }

            DB::commit();

            $receiver = Auth::user();
            $receiverName = $receiver ? $receiver->name : 'Someone';
            PushNotificationService::send(
                $senderId,
                "রিকোয়েস্ট গ্রহণ করা হয়েছে",
                "{$receiverName} আপনার ফ্রেন্ড রিকোয়েস্ট এক্সেপ্ট করেছে",
                "friend_accepted",
                ['friend_id' => $receiverId, 'request_id' => $friendRequest->id]
            );

            Log::info('Friend request accepted', [
                'request_id' => $friendRequest->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $this->successResponse(
                [
                    'request_id' => $friendRequest->id,
                    'friend' => [
                        'id' => $sender->id,
                        'name' => $sender->name,
                        'email' => $sender->email,
                        'photo' => $this->resolvePhoto($sender->photo),
                    ],
                    'status' => 'accepted',
                ],
                'Friend request accepted successfully'
            );

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Accept request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sender_id' => $request->sender_id ?? null
            ]);
            return $this->errorResponse('Failed to accept friend request', 500);
        }
    }

    /* =========================================================
     | F. REJECT FRIEND REQUEST
     | Endpoint: POST /api/user/friend/request/reject
     | Body: { "sender_id": 123 }
     |=========================================================*/
    public function rejectRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        try {
            $senderId = (int) $request->sender_id;
            $receiverId = Auth::id();

            // Option 1: Delete (recommended for cleaner database)
            $deleted = ChatRequest::where([
                'sender_id'   => $senderId,
                'receiver_id' => $receiverId,
                'status'      => 'pending'
            ])->delete();

            // Option 2: Mark as rejected (if you want to keep history)
            // $updated = ChatRequest::where([
            //     'sender_id'   => $senderId,
            //     'receiver_id' => $receiverId,
            //     'status'      => 'pending'
            // ])->update(['status' => 'rejected']);

            if ($deleted) {
                Log::info('Friend request rejected', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);

                return $this->successResponse(
                    null,
                    'Friend request rejected successfully'
                );
            } else {
                return $this->errorResponse(
                    'Friend request not found',
                    404
                );
            }

        } catch (Throwable $e) {
            Log::error('Reject request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to reject friend request', 500);
        }
    }

    /* =========================================================
     | G. FRIENDS LIST
     | Endpoint: GET /api/friends
     |=========================================================*/
    public function friends()
    {
        try {
            $authId = Auth::id();

            $friendRequests = ChatRequest::where('status', 'accepted')
                ->where(function ($q) use ($authId) {
                    $q->where('sender_id', $authId)
                      ->orWhere('receiver_id', $authId);
                })
                ->with(['sender:id,name,email,photo', 'receiver:id,name,email,photo'])
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($friendRequests->isEmpty()) {
                return $this->successResponse([], 'No friends yet');
            }

            $friends = $friendRequests->map(function ($r) use ($authId) {
                $friend = $r->sender_id === $authId ? $r->receiver : $r->sender;

                // Check if friend still exists
                if (!$friend) {
                    return null;
                }

                return [
                    'id'    => $friend->id,
                    'name'  => $friend->name,
                    'email' => $friend->email,
                    'photo' => $this->resolvePhoto($friend->photo),
                    'friendship_since' => $r->updated_at->toIso8601String(),
                ];
            })->filter()->values(); // Remove null and reindex

            Log::info('Friends list loaded', [
                'user_id' => $authId,
                'count' => $friends->count()
            ]);

            return $this->successResponse(
                $friends,
                $friends->count() . ' friend(s) found'
            );

        } catch (Throwable $e) {
            Log::error('Friends list error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to load friends list', 500);
        }
    }

    /* =========================================================
     | H. FRIENDS COUNT
     | Endpoint: GET /api/friends/count
     |=========================================================*/
    public function friendsCount()
    {
        try {
            $authId = Auth::id();

            $count = ChatRequest::where('status', 'accepted')
                ->where(function ($q) use ($authId) {
                    $q->where('sender_id', $authId)
                      ->orWhere('receiver_id', $authId);
                })
                ->count();

            return $this->successResponse(
                ['count' => $count],
                'Friend count retrieved'
            );

        } catch (Throwable $e) {
            Log::error('Friend count error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to get friend count', 500);
        }
    }

    /* =========================================================
     | I. UNFRIEND
     | Endpoint: POST /api/unfriend
     | Body: { "friend_id": 123 }
     |=========================================================*/
    public function unfriend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'friend_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        DB::beginTransaction();

        try {
            $authId = Auth::id();
            $friendId = (int) $request->friend_id;

            if ($authId === $friendId) {
                return $this->errorResponse('Invalid friend ID', 400);
            }

            $deleted = ChatRequest::where('status', 'accepted')
                ->where(function ($q) use ($authId, $friendId) {
                    $q->where(function ($sq) use ($authId, $friendId) {
                        $sq->where('sender_id', $authId)
                           ->where('receiver_id', $friendId);
                    })->orWhere(function ($sq) use ($authId, $friendId) {
                        $sq->where('sender_id', $friendId)
                           ->where('receiver_id', $authId);
                    });
                })
                ->delete();

            if ($deleted) {
                DB::commit();

                Log::info('Friend removed', [
                    'user_id' => $authId,
                    'friend_id' => $friendId
                ]);

                return $this->successResponse(null, 'Friend removed successfully');
            } else {
                DB::rollBack();
                return $this->errorResponse('Friend not found', 404);
            }

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Unfriend error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to remove friend', 500);
        }
    }

    /* =========================================================
     | J. SENT FRIEND REQUESTS (VIEW OWN SENT REQUESTS)
     | Endpoint: GET /api/user/friend/request/sent
     |=========================================================*/
    public function sentRequests()
    {
        try {
            $authId = Auth::id();

            $requests = ChatRequest::where('sender_id', $authId)
                ->where('status', 'pending')
                ->with('receiver:id,name,email,photo')
                ->latest()
                ->get();

            if ($requests->isEmpty()) {
                return $this->successResponse([], 'No sent friend requests');
            }

            $formattedRequests = $requests->map(function ($r) {
                if (!$r->receiver) {
                    return null;
                }

                return [
                    'id' => $r->id,
                    'receiver' => [
                        'id'    => $r->receiver->id,
                        'name'  => $r->receiver->name,
                        'email' => $r->receiver->email,
                        'photo' => $this->resolvePhoto($r->receiver->photo),
                    ],
                    'status' => $r->status,
                    'created_at' => $r->created_at->toIso8601String(),
                ];
            })->filter()->values();

            return $this->successResponse(
                $formattedRequests,
                $formattedRequests->count() . ' sent request(s)'
            );

        } catch (Throwable $e) {
            Log::error('Sent requests error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to load sent requests', 500);
        }
    }

    /* =========================================================
     | HELPER METHODS
     |=========================================================*/

    /**
     * Resolve photo URL with better fallback handling
     */
    private function resolvePhoto(?string $photo): string
    {
        if (!$photo || $photo === '') {
            return asset('uploads/avator.jpg');
        }

        // If already full URL, return as is
        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
            return $photo;
        }

        // If absolute path, extract filename
        if (str_starts_with($photo, '/')) {
            $photo = basename($photo);
        }

        // Clean filename
        $photo = ltrim($photo, '/');

        // Return full asset URL
        return asset('uploads/profile/' . $photo);
    }

    /**
     * Format user with friend request status
     */
    private function formatUserWithStatus(User $user, int $authId): array
    {
        $request = ChatRequest::where(function ($q) use ($authId, $user) {
            $q->where(function ($sq) use ($authId, $user) {
                $sq->where('sender_id', $authId)
                   ->where('receiver_id', $user->id);
            })->orWhere(function ($sq) use ($authId, $user) {
                $sq->where('sender_id', $user->id)
                   ->where('receiver_id', $authId);
            });
        })->first();

        $friendStatus = 'none';
        $requestSentByMe = false;
        $canSendRequest = true;

        if ($request) {
            $friendStatus = $request->status;
            $requestSentByMe = $request->sender_id === $authId;

            // Can only send request if no active request exists
            $canSendRequest = $request->status === 'rejected';
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $this->resolvePhoto($user->photo),
            'friend_status' => $friendStatus,
            'request_sent_by_me' => $requestSentByMe,
            'can_send_request' => $canSendRequest,
        ];
    }

    /**
     * Success response helper
     */
    private function successResponse($data, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message
        ], $code);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
