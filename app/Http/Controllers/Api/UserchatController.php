<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRequest;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class UserchatController extends Controller
{
    /**
     * Resolve user profile photo URL safely.
     */
    private function resolvePhoto($photo): string
    {
        if (!$photo || $photo === '') {
            return asset('uploads/avator.jpg');
        }

        $photoStr = trim((string)$photo);
        if (str_starts_with($photoStr, 'http://') || str_starts_with($photoStr, 'https://')) {
            return $photoStr;
        }

        return asset('uploads/profile/' . ltrim($photoStr, '/'));
    }

    /**
     * Get Friend List for Chat
     * Returns all accepted friends for the authenticated user
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

            Log::info("Fetching chat list for user: $userId");

            // Fetch accepted friends
            $friends = ChatRequest::where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })
                ->where('status', 'accepted')
                ->with(['sender', 'receiver'])
                ->get();

            // Build contact list
            $contacts = $friends->map(function ($item) use ($userId) {
                $friend = $item->sender_id == $userId ? $item->receiver : $item->sender;

                if (!$friend) {
                    return null;
                }

                $isVerified = (bool) $friend->is_verified;
                $statusText = $isVerified ? 'verified' : 'unverified';

                return [
                    'id'                  => $friend->id,
                    'name'                => $friend->name ?? 'Unknown User',
                    'email'               => $friend->email ?? '',
                    'photo'               => $this->resolvePhoto($friend->photo),
                    'image'               => $this->resolvePhoto($friend->photo),
                    'photo_url'           => $this->resolvePhoto($friend->photo),
                    'role'                => $friend->role ?? 'user',
                    'is_verified'         => $isVerified,
                    'kyc_approved'        => $isVerified,
                    'kyc_status'          => $statusText,
                    'verification_status' => $statusText,
                    'status'              => $statusText,
                ];
            })->filter()->values();

            Log::info("Found " . $contacts->count() . " contacts for user: $userId");

            return response()->json([
                'success' => true,
                'message' => 'Friend list fetched successfully',
                'data' => $contacts
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in frontend_chat_list: " . $e->getMessage());

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
     */
    public function frontend_chat_submit(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer|exists:users,id',
                'message' => 'required_without:image|nullable|string|max:5000',
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
            $receiverId = (int) $request->receiver_id;

            if (!$senderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Prevent sending message to yourself
            if ($senderId == $receiverId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot send message to yourself'
                ], 400);
            }

            // Check if receiver exists
            $receiver = User::find($receiverId);
            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

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

                    if (!$image->isValid()) {
                        throw new Exception('Invalid image file');
                    }

                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                    $uploadPath = public_path('uploads/chat');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

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

            // Send Push Notification to Receiver
            $sender = Auth::user();
            $senderName = $sender ? $sender->name : 'Friend';
            $msgBody = !empty($chatMessage->message) ? $chatMessage->message : '📷 Photo';

            PushNotificationService::send(
                $receiverId,
                "New message from {$senderName}",
                $msgBody,
                "chat_message",
                ['chat_id' => $chatMessage->id, 'sender_id' => $senderId]
            );

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
                    'created_at' => ($chatMessage->created_at ? $chatMessage->created_at->format('h:i A') : ''),
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error("Error in frontend_chat_submit: " . $e->getMessage());

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
            $userId = (int) $request->user_id;

            if (!$authId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Prevent getting messages with yourself
            if ($authId == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user ID'
                ], 400);
            }

            // Check if the other user exists
            $otherUser = User::find($userId);
            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

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
                    'created_at' => ($msg->created_at ? $msg->created_at->format('h:i A') : ''),
                    'date' => ($msg->created_at ? $msg->created_at->format('M d, Y') : ''),
                ];
            });

            Log::info("Found " . $messages->count() . " messages between $authId and $userId");

            return response()->json([
                'success' => true,
                'message' => 'Messages fetched successfully',
                'data' => $formatted
            ], 200);

        } catch (Exception $e) {
            Log::error("Error in frontend_chat_messages: " . $e->getMessage());

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
     */
    public function getUnreadCounts()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Get unread message count grouped by sender
            $counts = ChatMessage::select('sender_id', DB::raw('COUNT(*) as unread_count'))
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->groupBy('sender_id')
                ->get()
                ->pluck('unread_count', 'sender_id');

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
            ], 200);
        }
    }

    /**
     * Delete Message
     * Allows user to delete their own sent messages
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

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
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
                    @unlink($imagePath);
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

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
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
     */
    public function getLastMessages()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Get all friends
            $friends = ChatRequest::where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->where('status', 'accepted')
            ->with(['sender', 'receiver'])
            ->get();

            // Collect all friend IDs
            $friendIds = $friends->map(function ($item) use ($userId) {
                $friend = $item->sender_id == $userId ? $item->receiver : $item->sender;
                return $friend ? $friend->id : null;
            })->filter()->unique()->toArray();

            // Fetch unread counts in bulk
            $unreadCounts = collect();
            if (!empty($friendIds)) {
                $unreadCounts = ChatMessage::where('receiver_id', $userId)
                    ->whereIn('sender_id', $friendIds)
                    ->where('is_read', false)
                    ->select('sender_id', DB::raw('COUNT(*) as unread_count'))
                    ->groupBy('sender_id')
                    ->pluck('unread_count', 'sender_id');
            }

            // Fetch last messages in bulk
            $lastMessages = collect();
            if (!empty($friendIds)) {
                $latestMessageIds = ChatMessage::where(function ($q) use ($userId) {
                        $q->where('sender_id', $userId)
                          ->orWhere('receiver_id', $userId);
                    })
                    ->where(function ($q) use ($friendIds) {
                        $q->whereIn('sender_id', $friendIds)
                          ->orWhereIn('receiver_id', $friendIds);
                    })
                    ->select(DB::raw('MAX(id) as max_id'))
                    ->groupBy(DB::raw('CASE WHEN sender_id = ' . $userId . ' THEN receiver_id ELSE sender_id END'))
                    ->pluck('max_id');

                $lastMessages = ChatMessage::whereIn('id', $latestMessageIds)
                    ->get()
                    ->keyBy(function ($msg) use ($userId) {
                        return $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;
                    });
            }

            $chatList = $friends->map(function ($item) use ($userId, $unreadCounts, $lastMessages) {
                $friend = $item->sender_id == $userId ? $item->receiver : $item->sender;

                if (!$friend) {
                    return null;
                }

                $lastMessage = $lastMessages->get($friend->id);
                $unreadCount = $unreadCounts->get($friend->id, 0);

                $isVerified = (bool) $friend->is_verified;
                $statusText = $isVerified ? 'verified' : 'unverified';

                return [
                    'id'                  => $friend->id,
                    'name'                => $friend->name ?? 'Unknown User',
                    'email'               => $friend->email ?? '',
                    'image'               => $this->resolvePhoto($friend->photo),
                    'photo'               => $this->resolvePhoto($friend->photo),
                    'photo_url'           => $this->resolvePhoto($friend->photo),
                    'role'                => $friend->role ?? 'user',
                    'is_verified'         => $isVerified,
                    'kyc_approved'        => $isVerified,
                    'kyc_status'          => $statusText,
                    'verification_status' => $statusText,
                    'status'              => $statusText,
                    'last_message'        => $lastMessage ? [
                        'text'    => $lastMessage->message ?? ($lastMessage->image ? '📷 Photo' : ''),
                        'time'    => ($lastMessage->created_at ? $lastMessage->created_at->format('h:i A') : ''),
                        'is_sent' => $lastMessage->sender_id == $userId,
                    ] : null,
                    'unread_count'        => $unreadCount,
                ];
            })->filter()->values();

            // Sort by last message time (most recent first)
            $chatList = $chatList->sortByDesc(function ($item) {
                return $item['last_message']['time'] ?? '';
            })->values();

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
