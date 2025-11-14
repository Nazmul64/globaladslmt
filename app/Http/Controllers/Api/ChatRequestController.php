<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatRequestController extends Controller
{
    /**
     * A. Search users - Fixed with proper bidirectional friend status check
     */
    public function search(Request $request)
    {
        try {
            $query = $request->query('q');

            if (!$query || trim($query) === '') {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No search query provided'
                ]);
            }

            $currentUserId = Auth::id();

            // Search users excluding current user
            $users = User::where('id', '!=', $currentUserId)
                        ->where(function($q) use ($query) {
                            $q->where('name', 'LIKE', "%{$query}%")
                              ->orWhere('email', 'LIKE', "%{$query}%");
                        })
                        ->select('id', 'name', 'email', 'photo')
                        ->limit(20)
                        ->get();

            $usersWithStatus = $users->map(function ($user) use ($currentUserId) {
                // Check bidirectional friend requests
                $myRequestToThem = ChatRequest::where('sender_id', $currentUserId)
                                              ->where('receiver_id', $user->id)
                                              ->first();

                $theirRequestToMe = ChatRequest::where('sender_id', $user->id)
                                               ->where('receiver_id', $currentUserId)
                                               ->first();

                $friendStatus = 'none';
                $requestSentByMe = false;

                // Priority 1: Check if we are already friends (accepted status in either direction)
                if ($myRequestToThem && $myRequestToThem->status === 'accepted') {
                    $friendStatus = 'friend';
                    $requestSentByMe = false; // Not relevant when already friends
                }
                elseif ($theirRequestToMe && $theirRequestToMe->status === 'accepted') {
                    $friendStatus = 'friend';
                    $requestSentByMe = false; // Not relevant when already friends
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
            });

            Log::info('User search completed', [
                'query' => $query,
                'results_count' => $usersWithStatus->count(),
                'user_id' => $currentUserId
            ]);

            return response()->json([
                'success' => true,
                'data' => $usersWithStatus,
                'message' => $usersWithStatus->count() . ' users found'
            ]);

        } catch (Exception $e) {
            Log::error('User search error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error searching users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * B. Send Friend Request - Fixed with comprehensive duplicate prevention
     */
    public function sendFriendRequest(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|integer|exists:users,id'
            ]);

            $senderId = Auth::id();
            $receiverId = $request->receiver_id;

            Log::info('Friend request attempt', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            // Validation 1: Cannot send request to yourself
            if ($senderId == $receiverId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot send a friend request to yourself.'
                ], 400);
            }

            // Validation 2: Check if I already sent a request to them
            $myRequestToThem = ChatRequest::where('sender_id', $senderId)
                                         ->where('receiver_id', $receiverId)
                                         ->first();

            if ($myRequestToThem) {
                if ($myRequestToThem->status === 'pending') {
                    Log::warning('Duplicate friend request attempt', [
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'existing_status' => 'pending'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'You already sent a friend request to this user.'
                    ], 409);
                }
                elseif ($myRequestToThem->status === 'accepted') {
                    Log::warning('Friend request to existing friend', [
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'existing_status' => 'accepted'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'You are already friends with this user.'
                    ], 409);
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
                        'receiver_id' => $senderId,
                        'existing_status' => 'pending'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'This user already sent you a friend request. Please check your Friend Requests page to accept it.'
                    ], 409);
                }
                elseif ($theirRequestToMe->status === 'accepted') {
                    Log::warning('Friend request to existing friend (reverse)', [
                        'sender_id' => $receiverId,
                        'receiver_id' => $senderId,
                        'existing_status' => 'accepted'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'You are already friends with this user.'
                    ], 409);
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

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $friendRequest->id,
                    'sender_id' => $friendRequest->sender_id,
                    'receiver_id' => $friendRequest->receiver_id,
                    'status' => $friendRequest->status,
                    'created_at' => $friendRequest->created_at
                ],
                'message' => 'Friend request sent successfully.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in friend request', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Friend request error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending friend request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * C. Cancel Friend Request - Only cancel requests I sent
     */
    public function cancelFriendRequest(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|integer|exists:users,id'
            ]);

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

                return response()->json([
                    'success' => true,
                    'message' => 'Friend request cancelled successfully.'
                ], 200);
            }

            Log::warning('Cancel request failed - No pending request found', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No pending friend request found to cancel.'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Cancel friend request error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling friend request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * D. Get Received Pending Friend Requests
     */
    public function sendFriendRequestaccept()
    {
        try {
            $userId = Auth::id();

            $requests = ChatRequest::where('receiver_id', $userId)
                                   ->where('status', 'pending')
                                   ->with('sender:id,name,email,photo')
                                   ->orderBy('created_at', 'desc')
                                   ->get();

            // Transform data for frontend
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
                        'photo' => $request->sender->photo ? url($request->sender->photo) : null,
                    ]
                ];
            });

            Log::info('Fetched pending friend requests', [
                'user_id' => $userId,
                'count' => $formattedRequests->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $formattedRequests,
                'message' => 'Pending friend requests retrieved successfully.'
            ], 200);

        } catch (Exception $e) {
            Log::error('Fetch friend requests error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching friend requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * E. Accept Friend Request
     */
    public function acceptRequest(Request $request)
    {
        try {
            $request->validate([
                'sender_id' => 'required|integer|exists:users,id'
            ]);

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

                return response()->json([
                    'success' => false,
                    'message' => 'Friend request not found or already processed.'
                ], 404);
            }

            // Update status to accepted
            $friendRequest->update(['status' => 'accepted']);

            Log::info('Friend request accepted successfully', [
                'request_id' => $friendRequest->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $friendRequest->id,
                    'sender_id' => $friendRequest->sender_id,
                    'receiver_id' => $friendRequest->receiver_id,
                    'status' => $friendRequest->status,
                    'updated_at' => $friendRequest->updated_at
                ],
                'message' => 'Friend request accepted successfully.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Accept friend request error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error accepting friend request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * F. Reject/Delete Friend Request
     */
    public function rejectRequest(Request $request)
    {
        try {
            $request->validate([
                'sender_id' => 'required|integer|exists:users,id'
            ]);

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

                return response()->json([
                    'success' => true,
                    'message' => 'Friend request rejected successfully.'
                ], 200);
            }

            Log::warning('Reject request failed - Request not found', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Friend request not found or already processed.'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Reject friend request error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting friend request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * G. Get Accepted Friends List
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
                ];
            });

            Log::info('Fetched friends list', [
                'user_id' => $userId,
                'friends_count' => $friendsList->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $friendsList,
                'message' => 'Friends list retrieved successfully.'
            ], 200);

        } catch (Exception $e) {
            Log::error('Fetch friends error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching friends list: ' . $e->getMessage()
            ], 500);
        }
    }
}
