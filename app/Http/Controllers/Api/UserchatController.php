<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class UserchatController extends Controller
{
    /**
     * Get Friend List for Chat (Only Regular Users - Role: user)
     * Returns all accepted friends for the authenticated user
     * Filters out admin users from the list
     */
    public function frontend_chat_list()
    {
        try {
            $userId = Auth::id();
            $currentUser = Auth::user();

            if (!$userId || !$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if current user has 'user' role
            if ($currentUser->role !== 'user') {
                Log::warning("Non-user role attempting to access chat: User ID $userId with role: {$currentUser->role}");

                return response()->json([
                    'success' => false,
                    'message' => 'Chat is only available for regular users.'
                ], 403);
            }

            Log::info("Fetching chat list for user: $userId (role: {$currentUser->role})");

            // Get all accepted friend requests where both users have 'user' role
            $friends = ChatRequest::where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->where('status', 'accepted')
            ->with(['sender' => function ($query) {
                $query->where('role', 'user'); // Only get users with 'user' role
            }, 'receiver' => function ($query) {
                $query->where('role', 'user'); // Only get users with 'user' role
            }])
            ->get();

            // Map to contact list and filter out non-user roles
            $contacts = $friends->map(function ($item) use ($userId) {
                // Get the friend (not the current user)
                $user = $item->sender_id == $userId ? $item->receiver : $item->sender;

                // Check if user exists
                if (!$user) {
                    Log::warning("User not found in chat request: " . $item->id);
                    return null;
                }

                // Double check role (extra safety)
                if ($user->role !== 'user') {
                    Log::info("Filtering out non-user from chat list: User ID {$user->id} with role: {$user->role}");
                    return null;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Unknown User',
                    'email' => $user->email ?? '',
                    'image' => $user->image
                        ? asset('uploads/profile/' . $user->image)
                        : null,
                    'role' => $user->role, // Include role for debugging
                ];
            })->filter()->values(); // Remove null values

            Log::info("Found " . $contacts->count() . " user-role contacts for user: $userId");

            return response()->json([
                'success' => true,
                'message' => 'Friend list fetched successfully',
                'data' => $contacts
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in frontend_chat_list: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch friend list',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Send Message (text or image)
     * Allows sending text messages and/or images to friends
     * Only works between users with 'user' role
     */
    public function frontend_chat_submit(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer|exists:users,id',
                'message' => 'required_without:image|string|max:5000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            ], [
                'receiver_id.required' => 'Receiver ID is required',
                'receiver_id.exists' => 'Receiver user not found',
                'message.required_without' => 'Message or image is required',
                'message.max' => 'Message is too long (max 5000 characters)',
                'image.image' => 'File must be an image',
                'image.mimes' => 'Image must be: jpeg, png, jpg, gif, or webp',
                'image.max' => 'Image size must not exceed 5MB',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $senderId = Auth::id();
            $sender = Auth::user();
            $receiverId = (int) $request->receiver_id;

            if (!$senderId || !$sender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if sender has 'user' role
            if ($sender->role !== 'user') {
                Log::warning("Non-user role attempting to send message: User ID $senderId with role: {$sender->role}");

                return response()->json([
                    'success' => false,
                    'message' => 'Only regular users can send messages.'
                ], 403);
            }

            // Prevent sending message to yourself
            if ($senderId == $receiverId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot send message to yourself'
                ], 400);
            }

            // Check if receiver exists and has 'user' role
            $receiver = User::find($receiverId);

            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            if ($receiver->role !== 'user') {
                Log::warning("Attempt to send message to non-user: Receiver ID $receiverId with role: {$receiver->role}");

                return response()->json([
                    'success' => false,
                    'message' => 'You can only send messages to regular users.'
                ], 403);
            }

            Log::info("User $senderId (role: user) sending message to user $receiverId (role: user)");

            // Check if they are friends
            $areFriends = ChatRequest::where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $senderId)
                       ->where('receiver_id', $receiverId);
                })
                ->orWhere(function ($sub) use ($senderId, $receiverId) {
                    $sub->where('sender_id', $receiverId)
                       ->where('receiver_id', $senderId);
                });
            })
            ->where('status', 'accepted')
            ->exists();

            if (!$areFriends) {
                Log::warning("Users $senderId and $receiverId are not friends");

                return response()->json([
                    'success' => false,
                    'message' => 'You are not friends with this user'
                ], 403);
            }

            // Create new message
            $chatMessage = new ChatMessage();
            $chatMessage->sender_id = $senderId;
            $chatMessage->receiver_id = $receiverId;
            $chatMessage->message = $request->message ?? '';
            $chatMessage->is_read = false;

            // Handle image upload
            if ($request->hasFile('image')) {
                try {
                    $image = $request->file('image');

                    // Validate image
                    if (!$image->isValid()) {
                        throw new Exception('Invalid image file');
                    }

                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                    // Create directory if not exists
                    $uploadPath = public_path('uploads/chat');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    // Move image to upload directory
                    $image->move($uploadPath, $imageName);
                    $chatMessage->image = $imageName;

                    Log::info("Image uploaded: $imageName");

                } catch (Exception $e) {
                    Log::error("Image upload error: " . $e->getMessage());

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload image: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Save message to database
            $chatMessage->save();

            Log::info("Message saved with ID: " . $chatMessage->id);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $chatMessage->id,
                    'message' => $chatMessage->message,
                    'image' => $chatMessage->image
                        ? asset('uploads/chat/' . $chatMessage->image)
                        : null,
                    'is_sent' => true,
                    'created_at' => $chatMessage->created_at->format('h:i A'),
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error("Error in frontend_chat_submit: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get Chat Messages Between Two Users
     * Returns all messages between authenticated user and specified user
     * Only works if both users have 'user' role
     */
    public function frontend_chat_messages(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
            ], [
                'user_id.required' => 'User ID is required',
                'user_id.exists' => 'User not found',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $authId = Auth::id();
            $authUser = Auth::user();
            $userId = (int) $request->user_id;

            if (!$authId || !$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if authenticated user has 'user' role
            if ($authUser->role !== 'user') {
                Log::warning("Non-user role attempting to access messages: User ID $authId with role: {$authUser->role}");

                return response()->json([
                    'success' => false,
                    'message' => 'Chat is only available for regular users.'
                ], 403);
            }

            // Prevent getting messages with yourself
            if ($authId == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user ID'
                ], 400);
            }

            // Check if the other user exists and has 'user' role
            $otherUser = User::find($userId);

            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($otherUser->role !== 'user') {
                Log::warning("Attempt to get messages with non-user: User ID $userId with role: {$otherUser->role}");

                return response()->json([
                    'success' => false,
                    'message' => 'You can only chat with regular users.'
                ], 403);
            }

            Log::info("Fetching messages between user $authId and user $userId (both have 'user' role)");

            // Check if they are friends
            $areFriends = ChatRequest::where(function ($q) use ($authId, $userId) {
                $q->where(function ($sub) use ($authId, $userId) {
                    $sub->where('sender_id', $authId)
                       ->where('receiver_id', $userId);
                })
                ->orWhere(function ($sub) use ($authId, $userId) {
                    $sub->where('sender_id', $userId)
                       ->where('receiver_id', $authId);
                });
            })
            ->where('status', 'accepted')
            ->exists();

            if (!$areFriends) {
                Log::warning("Users $authId and $userId are not friends");

                return response()->json([
                    'success' => false,
                    'message' => 'You are not friends with this user'
                ], 403);
            }

            // Get all messages between two users
            $messages = ChatMessage::where(function ($q) use ($authId, $userId) {
                $q->where(function ($sub) use ($authId, $userId) {
                    $sub->where('sender_id', $authId)
                       ->where('receiver_id', $userId);
                })
                ->orWhere(function ($sub) use ($authId, $userId) {
                    $sub->where('sender_id', $userId)
                       ->where('receiver_id', $authId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

            // Mark unread messages as read
            $markedAsRead = ChatMessage::where('sender_id', $userId)
                ->where('receiver_id', $authId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            if ($markedAsRead > 0) {
                Log::info("Marked $markedAsRead messages as read");
            }

            // Format messages for response
            $formatted = $messages->map(function ($msg) use ($authId) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message ?? '',
                    'image' => $msg->image
                        ? asset('uploads/chat/' . $msg->image)
                        : null,
                    'is_sent' => $msg->sender_id == $authId,
                    'is_read' => (bool) $msg->is_read,
                    'created_at' => $msg->created_at->format('h:i A'),
                    'date' => $msg->created_at->format('M d, Y'),
                ];
            });

            Log::info("Found " . $messages->count() . " messages");

            return response()->json([
                'success' => true,
                'message' => 'Messages fetched successfully',
                'data' => $formatted
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in frontend_chat_messages: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get Unread Message Counts
     * Returns count of unread messages grouped by sender
     * Only counts messages from users with 'user' role
     */
    public function getUnreadCounts()
    {
        try {
            $userId = Auth::id();
            $currentUser = Auth::user();

            if (!$userId || !$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if current user has 'user' role
            if ($currentUser->role !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat is only available for regular users.',
                    'data' => []
                ], 403);
            }

            // Get unread message count grouped by sender
            // Only count messages from users with 'user' role
            $counts = ChatMessage::select('chat_messages.sender_id', DB::raw('COUNT(*) as unread_count'))
                ->join('users', 'chat_messages.sender_id', '=', 'users.id')
                ->where('chat_messages.receiver_id', $userId)
                ->where('chat_messages.is_read', false)
                ->where('users.role', 'user') // Only count messages from regular users
                ->groupBy('chat_messages.sender_id')
                ->get()
                ->pluck('unread_count', 'sender_id');

            Log::info("Unread counts for user $userId (from user-role only): " . json_encode($counts));

            return response()->json([
                'success' => true,
                'message' => 'Unread count fetched successfully',
                'data' => $counts
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in getUnreadCounts: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread counts',
                'data' => []
            ], 200); // Return empty counts instead of error
        }
    }

    /**
     * Delete Message
     * Allows user to delete their own sent messages
     * Only works for users with 'user' role
     */
    public function deleteMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message_id' => 'required|integer|exists:chat_messages,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $userId = Auth::id();
            $currentUser = Auth::user();

            if (!$userId || !$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if current user has 'user' role
            if ($currentUser->role !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only regular users can delete messages.'
                ], 403);
            }

            $message = ChatMessage::find($request->message_id);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            // Only sender can delete their own message
            if ($message->sender_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own messages'
                ], 403);
            }

            // Delete image if exists
            if ($message->image) {
                $imagePath = public_path('uploads/chat/' . $message->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                    Log::info("Deleted image: " . $message->image);
                }
            }

            $message->delete();
            Log::info("Message deleted: " . $request->message_id . " by user: $userId");

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in deleteMessage: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message'
            ], 500);
        }
    }

    /**
     * Mark Message as Read
     * Marks a specific message as read by the receiver
     * Only works for users with 'user' role
     */
    public function markAsRead(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message_id' => 'required|integer|exists:chat_messages,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $userId = Auth::id();
            $currentUser = Auth::user();

            if (!$userId || !$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if current user has 'user' role
            if ($currentUser->role !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only regular users can mark messages as read.'
                ], 403);
            }

            $message = ChatMessage::find($request->message_id);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            // Only receiver can mark message as read
            if ($message->receiver_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            $message->is_read = true;
            $message->save();

            Log::info("Message marked as read: " . $request->message_id . " by user: $userId");

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in markAsRead: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark message as read'
            ], 500);
        }
    }

    /**
     * Get Last Message with Each Friend
     * Returns chat list with last message preview and unread count
     * Only includes users with 'user' role
     */
    public function getLastMessages()
    {
        try {
            $userId = Auth::id();
            $currentUser = Auth::user();

            if (!$userId || !$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Check if current user has 'user' role
            if ($currentUser->role !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat is only available for regular users.'
                ], 403);
            }

            // Get all friends with 'user' role
            $friends = ChatRequest::where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->where('status', 'accepted')
            ->with(['sender' => function ($query) {
                $query->where('role', 'user');
            }, 'receiver' => function ($query) {
                $query->where('role', 'user');
            }])
            ->get();

            $chatList = $friends->map(function ($item) use ($userId) {
                $friend = $item->sender_id == $userId ? $item->receiver : $item->sender;

                if (!$friend) {
                    return null;
                }

                // Double check role
                if ($friend->role !== 'user') {
                    Log::info("Filtering out non-user from last messages: User ID {$friend->id} with role: {$friend->role}");
                    return null;
                }

                // Get last message
                $lastMessage = ChatMessage::where(function ($q) use ($userId, $friend) {
                    $q->where(function ($sub) use ($userId, $friend) {
                        $sub->where('sender_id', $userId)
                           ->where('receiver_id', $friend->id);
                    })
                    ->orWhere(function ($sub) use ($userId, $friend) {
                        $sub->where('sender_id', $friend->id)
                           ->where('receiver_id', $userId);
                    });
                })
                ->latest()
                ->first();

                // Get unread count
                $unreadCount = ChatMessage::where('sender_id', $friend->id)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $friend->id,
                    'name' => $friend->name ?? 'Unknown User',
                    'email' => $friend->email ?? '',
                    'image' => $friend->image
                        ? asset('uploads/profile/' . $friend->image)
                        : null,
                    'role' => $friend->role,
                    'last_message' => $lastMessage ? [
                        'text' => $lastMessage->message ?? ($lastMessage->image ? '📷 Photo' : ''),
                        'time' => $lastMessage->created_at->format('h:i A'),
                        'is_sent' => $lastMessage->sender_id == $userId,
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            })->filter()->values(); // Remove null values

            // Sort by last message time (most recent first)
            $chatList = $chatList->sortByDesc(function ($item) {
                return $item['last_message']['time'] ?? '';
            })->values();

            Log::info("Chat list with last messages for user $userId (user-role only): " . $chatList->count() . " chats");

            return response()->json([
                'success' => true,
                'message' => 'Chat list fetched successfully',
                'data' => $chatList
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in getLastMessages: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chat list',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
