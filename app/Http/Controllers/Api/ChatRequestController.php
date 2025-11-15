<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ChatRequestController extends Controller
{
    /**
     * ========================================
     * A. SEARCH USERS
     * ========================================
     * Search users by name or email with friend status
     * GET: /api/user-search?q=search_query
     */
    public function search(Request $request)
    {
        try {
            $query = $request->query('q');

            // Return empty array if no query provided
            if (!$query || trim($query) === '') {
                return $this->successResponse([], 'No search query provided');
            }

            $currentUserId = Auth::id();
            $searchTerm = trim($query);

            // Search users excluding current user
            $users = User::where('id', '!=', $currentUserId)
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->select('id', 'name', 'email', 'photo')
                ->limit(20)
                ->get();

            // Map users with their friend status
            $usersWithStatus = $users->map(function ($user) use ($currentUserId) {
                return $this->getUserWithFriendStatus($user, $currentUserId);
            });

            Log::info('User search completed', [
                'query' => $searchTerm,
                'results_count' => $usersWithStatus->count(),
                'user_id' => $currentUserId
            ]);

            return $this->successResponse(
                $usersWithStatus,
                $usersWithStatus->count() . ' users found'
            );

        } catch (Exception $e) {
            Log::error('User search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error searching users: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * B. SEND FRIEND REQUEST
     * ========================================
     * Send a friend request to another user
     * POST: /api/user/friend/request
     * Body: { "receiver_id": 123 }
     */
    public function sendFriendRequest(Request $request)
    {
        try {
            // Validate input
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

            $senderId = Auth::id();
            $receiverId = $request->receiver_id;

            Log::info('Friend request attempt', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            // Validation 1: Cannot send request to yourself
            if ($senderId == $receiverId) {
                return $this->errorResponse(
                    'You cannot send a friend request to yourself.',
                    400
                );
            }

            // Validation 2: Check if I already sent a request to them
            $myRequestToThem = ChatRequest::where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->first();

            if ($myRequestToThem) {
                if ($myRequestToThem->status === 'pending') {
                    Log::warning('Duplicate friend request attempt', [
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId
                    ]);

                    return $this->errorResponse(
                        'You already sent a friend request to this user.',
                        409
                    );
                }

                if ($myRequestToThem->status === 'accepted') {
                    Log::warning('Friend request to existing friend', [
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId
                    ]);

                    return $this->errorResponse(
                        'You are already friends with this user.',
                        409
                    );
                }
            }

            // Validation 3: Check if they already sent a request to me
            $theirRequestToMe = ChatRequest::where('sender_id', $receiverId)
                ->where('receiver_id', $senderId)
                ->first();

            if ($theirRequestToMe) {
                if ($theirRequestToMe->status === 'pending') {
                    Log::warning('Reverse friend request exists', [
                        'sender_id' => $receiverId,
                        'receiver_id' => $senderId
                    ]);

                    return $this->errorResponse(
                        'This user already sent you a friend request. Please check your Friend Requests page to accept it.',
                        409
                    );
                }

                if ($theirRequestToMe->status === 'accepted') {
                    Log::warning('Already friends (reverse check)', [
                        'sender_id' => $receiverId,
                        'receiver_id' => $senderId
                    ]);

                    return $this->errorResponse(
                        'You are already friends with this user.',
                        409
                    );
                }
            }

            // All validations passed - Create new friend request
            $friendRequest = ChatRequest::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => 'pending',
            ]);

            Log::info('Friend request sent successfully', [
                'id' => $friendRequest->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $this->successResponse([
                'id' => $friendRequest->id,
                'sender_id' => $friendRequest->sender_id,
                'receiver_id' => $friendRequest->receiver_id,
                'status' => $friendRequest->status,
                'created_at' => $friendRequest->created_at,
            ], 'Friend request sent successfully.');

        } catch (Exception $e) {
            Log::error('Friend request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error sending friend request: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * C. CANCEL FRIEND REQUEST
     * ========================================
     * Cancel a pending friend request that I sent
     * POST: /api/cancel/friend/request
     * Body: { "receiver_id": 123 }
     */
    public function cancelFriendRequest(Request $request)
    {
        try {
            // Validate input
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

            $senderId = Auth::id();
            $receiverId = $request->receiver_id;

            // Find and delete the pending request that I sent
            $deleted = ChatRequest::where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'pending')
                ->delete();

            if ($deleted) {
                Log::info('Friend request cancelled successfully', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);

                return $this->successResponse(
                    null,
                    'Friend request cancelled successfully.'
                );
            }

            Log::warning('Cancel request failed - No pending request found', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $this->errorResponse(
                'No pending friend request found to cancel.',
                404
            );

        } catch (Exception $e) {
            Log::error('Cancel friend request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error cancelling friend request: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * D. GET RECEIVED FRIEND REQUESTS
     * ========================================
     * Get all pending friend requests sent to me
     * GET: /api/user/friend/request/accept/view
     */
    public function sendFriendRequestaccept()
    {
        try {
            $userId = Auth::id();

            // Fetch pending requests sent to me
            $requests = ChatRequest::where('receiver_id', $userId)
                ->where('status', 'pending')
                ->with('sender:id,name,email,photo')
                ->orderBy('created_at', 'desc')
                ->get();

            // Format the response
            $formattedRequests = $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'sender_id' => $request->sender_id,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                    'sender' => [
                        'id' => $request->sender->id,
                        'name' => $request->sender->name,
                        'email' => $request->sender->email,
                        'photo' => $request->sender->photo
                            ? url($request->sender->photo)
                            : null,
                    ]
                ];
            });

            Log::info('Fetched pending friend requests', [
                'user_id' => $userId,
                'count' => $formattedRequests->count()
            ]);

            return $this->successResponse(
                $formattedRequests,
                'Pending friend requests retrieved successfully.'
            );

        } catch (Exception $e) {
            Log::error('Fetch friend requests error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error fetching friend requests: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * E. ACCEPT FRIEND REQUEST
     * ========================================
     * Accept a pending friend request
     * POST: /api/user/friend/request/accept
     * Body: { "sender_id": 123 }
     */
    public function acceptRequest(Request $request)
    {
        try {
            // Validate input
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

            $receiverId = Auth::id();
            $senderId = $request->sender_id;

            // Find the pending friend request
            $friendRequest = ChatRequest::where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'pending')
                ->first();

            if (!$friendRequest) {
                Log::warning('Accept request failed - Request not found', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);

                return $this->errorResponse(
                    'Friend request not found or already processed.',
                    404
                );
            }

            // Update status to accepted
            $friendRequest->update(['status' => 'accepted']);

            Log::info('Friend request accepted successfully', [
                'request_id' => $friendRequest->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $this->successResponse([
                'id' => $friendRequest->id,
                'sender_id' => $friendRequest->sender_id,
                'receiver_id' => $friendRequest->receiver_id,
                'status' => $friendRequest->status,
                'updated_at' => $friendRequest->updated_at,
            ], 'Friend request accepted successfully.');

        } catch (Exception $e) {
            Log::error('Accept friend request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error accepting friend request: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * F. REJECT FRIEND REQUEST
     * ========================================
     * Reject/delete a pending friend request
     * POST: /api/user/friend/request/reject
     * Body: { "sender_id": 123 }
     */
    public function rejectRequest(Request $request)
    {
        try {
            // Validate input
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

            $receiverId = Auth::id();
            $senderId = $request->sender_id;

            // Find and delete the pending friend request
            $deleted = ChatRequest::where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'pending')
                ->delete();

            if ($deleted) {
                Log::info('Friend request rejected successfully', [
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId
                ]);

                return $this->successResponse(
                    null,
                    'Friend request rejected successfully.'
                );
            }

            Log::warning('Reject request failed - Request not found', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $this->errorResponse(
                'Friend request not found or already processed.',
                404
            );

        } catch (Exception $e) {
            Log::error('Reject friend request error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error rejecting friend request: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * G. GET FRIENDS LIST
     * ========================================
     * Get all accepted friends
     * GET: /api/friends
     */
    public function friends()
    {
        try {
            $userId = Auth::id();

            // Get all accepted friend requests where user is either sender or receiver
            $friendRequests = ChatRequest::where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })
                ->where('status', 'accepted')
                ->with([
                    'sender:id,name,email,photo',
                    'receiver:id,name,email,photo'
                ])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Extract the friend user (not the current user)
            $friendsList = $friendRequests->map(function ($request) use ($userId) {
                $friend = $request->sender_id == $userId
                    ? $request->receiver
                    : $request->sender;

                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'email' => $friend->email,
                    'photo' => $friend->photo ? url($friend->photo) : null,
                    'friendship_date' => $request->updated_at,
                    'friendship_since' => $request->updated_at->diffForHumans(),
                ];
            });

            Log::info('Fetched friends list', [
                'user_id' => $userId,
                'friends_count' => $friendsList->count()
            ]);

            return $this->successResponse(
                $friendsList,
                'Friends list retrieved successfully.'
            );

        } catch (Exception $e) {
            Log::error('Fetch friends error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error fetching friends list: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * H. GET FRIENDS COUNT
     * ========================================
     * Get total number of friends
     * GET: /api/friends/count
     */
    public function friendsCount()
    {
        try {
            $userId = Auth::id();

            $count = ChatRequest::where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })
                ->where('status', 'accepted')
                ->count();

            return $this->successResponse([
                'count' => $count
            ], 'Friends count retrieved successfully.');

        } catch (Exception $e) {
            Log::error('Fetch friends count error', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                'Error fetching friends count: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ========================================
     * I. UNFRIEND / REMOVE FRIEND
     * ========================================
     * Remove a friend (delete accepted friend request)
     * POST: /api/unfriend
     * Body: { "friend_id": 123 }
     */
    public function unfriend(Request $request)
    {
        try {
            // Validate input
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

            $userId = Auth::id();
            $friendId = $request->friend_id;

            // Find and delete the accepted friendship (bidirectional check)
            $deleted = ChatRequest::where(function ($q) use ($userId, $friendId) {
                    $q->where(function ($query) use ($userId, $friendId) {
                        $query->where('sender_id', $userId)
                              ->where('receiver_id', $friendId);
                    })
                    ->orWhere(function ($query) use ($userId, $friendId) {
                        $query->where('sender_id', $friendId)
                              ->where('receiver_id', $userId);
                    });
                })
                ->where('status', 'accepted')
                ->delete();

            if ($deleted) {
                Log::info('Friend removed successfully', [
                    'user_id' => $userId,
                    'friend_id' => $friendId
                ]);

                return $this->successResponse(
                    null,
                    'Friend removed successfully.'
                );
            }

            return $this->errorResponse(
                'Friendship not found.',
                404
            );

        } catch (Exception $e) {
            Log::error('Unfriend error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error removing friend: ' . $e->getMessage(),
                500
            );
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get user with friend status
     */
    private function getUserWithFriendStatus($user, $currentUserId)
    {
        // Check bidirectional friend requests
        $myRequestToThem = ChatRequest::where('sender_id', $currentUserId)
            ->where('receiver_id', $user->id)
            ->first();

        $theirRequestToMe = ChatRequest::where('sender_id', $user->id)
            ->where('receiver_id', $currentUserId)
            ->first();

        $friendStatus = 'none';
        $requestSentByMe = false;

        // Priority 1: Check if already friends (accepted in either direction)
        if ($myRequestToThem && $myRequestToThem->status === 'accepted') {
            $friendStatus = 'friend';
            $requestSentByMe = false;
        } elseif ($theirRequestToMe && $theirRequestToMe->status === 'accepted') {
            $friendStatus = 'friend';
            $requestSentByMe = false;
        }
        // Priority 2: Check if I have a pending request to them
        elseif ($myRequestToThem && $myRequestToThem->status === 'pending') {
            $friendStatus = 'pending';
            $requestSentByMe = true;
        }
        // Priority 3: Check if they have a pending request to me
        elseif ($theirRequestToMe && $theirRequestToMe->status === 'pending') {
            $friendStatus = 'pending';
            $requestSentByMe = false;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $user->photo ? url($user->photo) : null,
            'friend_status' => $friendStatus,
            'request_sent_by_me' => $requestSentByMe,
        ];
    }

    /**
     * Success response helper
     */
    private function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    /**
     * Error response helper
     */
    private function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
