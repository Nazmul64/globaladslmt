<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Usertoagentchat;
use App\Models\ChatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class UsertoagentChatController extends BaseController
{
    /**
     * Fetch agent list with accepted chat requests (JSON)
     */
    public function frontend_user_toagent_chat()
    {
        try {
            $user_id = auth()->id();

            if (!$user_id) {
                return $this->sendError('Unauthorized. Please login.', [], 401);
            }

            Log::info('=== Fetching Agents for User ===', ['user_id' => $user_id]);

            // Base columns
            $columns = ['id', 'name', 'email'];

            // Add optional columns if exist
            if (Schema::hasColumn('users', 'photo')) {
                $columns[] = 'photo';
            }
            if (Schema::hasColumn('users', 'status')) {
                $columns[] = 'status';
            }

            // Get all accepted agents
            $agents = User::where('role', 'agent')
                ->whereHas('receivedChatRequests', function ($query) use ($user_id) {
                    $query->where('sender_id', $user_id)
                        ->where('status', 'accepted');
                })
                ->select($columns)
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($agent) {
                    return [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'email' => $agent->email,
                        'photo' => $agent->photo ?? null,
                        'status' => $agent->status ?? 'offline',
                        'unread_count' => 0,
                    ];
                });

            Log::info('Agents fetched successfully.', ['count' => $agents->count()]);

            if ($agents->isEmpty()) {
                return $this->sendResponse([], 'No agents available. Send chat requests first.');
            }

            return $this->sendResponse($agents, 'Agent list fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching agents', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to fetch agents.', [], 500);
        }
    }

    /**
     * Send message (JSON)
     */
    public function frontend_chat_submit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer|exists:users,id',
                'message' => 'nullable|string|max:5000',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation error.', $validator->errors(), 422);
            }

            $validated = $validator->validated();
            $currentUserId = Auth::id();
            $receiverId = $validated['receiver_id'];

            Log::info('=== Sending Message ===', [
                'sender' => $currentUserId,
                'receiver' => $receiverId,
            ]);

            // Verify agent
            $receiver = User::where('id', $receiverId)
                ->where('role', 'agent')
                ->first();

            if (!$receiver) {
                return $this->sendError('Invalid agent selected.', [], 400);
            }

            // Verify accepted chat request
            $chatRequest = ChatRequest::where('sender_id', $currentUserId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'accepted')
                ->first();

            if (!$chatRequest) {
                return $this->sendError('Chat request not accepted yet.', [], 403);
            }

            if (empty($validated['message']) && !$request->hasFile('photo')) {
                return $this->sendError('Please enter a message or select a photo.', [], 400);
            }

            DB::beginTransaction();

            $chat = new Usertoagentchat();
            $chat->sender_id = $currentUserId;
            $chat->receiver_id = $receiverId;
            $chat->message = $validated['message'] ?? null;
            $chat->is_read = false;

            // Photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'chat_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $uploadPath = public_path('uploads/chat');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $photo->move($uploadPath, $photoName);
                $chat->photo = 'uploads/chat/' . $photoName;
            }

            $chat->save();
            DB::commit();

            return $this->sendResponse([
                'id' => $chat->id,
                'sender_id' => $chat->sender_id,
                'receiver_id' => $chat->receiver_id,
                'message' => $chat->message,
                'photo' => $chat->photo,
                'is_read' => $chat->is_read,
                'created_at' => $chat->created_at->toDateTimeString(),
            ], 'Message sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending message', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to send message.', [], 500);
        }
    }

    /**
     * Load chat messages (JSON)
     */
    public function frontend_chat_messages(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation error.', $validator->errors(), 422);
            }

            $validated = $validator->validated();
            $currentUserId = Auth::id();
            $receiverId = $validated['receiver_id'];

            Log::info('=== Loading Messages ===', [
                'user' => $currentUserId,
                'chat_with' => $receiverId,
            ]);

            // Verify agent
            $receiver = User::where('id', $receiverId)
                ->where('role', 'agent')
                ->first();

            if (!$receiver) {
                return $this->sendError('Invalid agent selected.', [], 400);
            }

            // Verify chat request
            $chatRequest = ChatRequest::where('sender_id', $currentUserId)
                ->where('receiver_id', $receiverId)
                ->where('status', 'accepted')
                ->first();

            if (!$chatRequest) {
                return $this->sendError('Chat request not accepted yet.', [], 403);
            }

            $messages = Usertoagentchat::where(function ($query) use ($currentUserId, $receiverId) {
                    $query->where('sender_id', $currentUserId)
                        ->where('receiver_id', $receiverId);
                })
                ->orWhere(function ($query) use ($currentUserId, $receiverId) {
                    $query->where('sender_id', $receiverId)
                        ->where('receiver_id', $currentUserId);
                })
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'sender_id' => $msg->sender_id,
                        'receiver_id' => $msg->receiver_id,
                        'message' => $msg->message,
                        'photo' => $msg->photo,
                        'is_read' => (bool) $msg->is_read,
                        'created_at' => $msg->created_at->toDateTimeString(),
                    ];
                });

            // Mark unread as read
            Usertoagentchat::where('receiver_id', $currentUserId)
                ->where('sender_id', $receiverId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return $this->sendResponse($messages, 'Messages fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error loading messages', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to load messages.', [], 500);
        }
    }

    /**
     * Get unread message counts (JSON)
     */
    public function getUnreadCounts()
    {
        try {
            $currentUserId = Auth::id();

            $unreadCounts = Usertoagentchat::select('sender_id', DB::raw('COUNT(*) as count'))
                ->where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->groupBy('sender_id')
                ->pluck('count', 'sender_id');

            $data = [
                'total_unread_count' => $unreadCounts->sum(),
                'unread_by_user' => $unreadCounts,
            ];

            return $this->sendResponse($data, 'Unread counts fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error getting unread counts', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to get unread counts.', [], 500);
        }
    }

    /**
     * Get last messages (JSON)
     */
    public function getLastMessages()
    {
        try {
            $currentUserId = Auth::id();

            $lastMessages = Usertoagentchat::select('usertoagentchats.*')
                ->whereIn('id', function ($query) use ($currentUserId) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('usertoagentchats')
                        ->where(function ($q) use ($currentUserId) {
                            $q->where('sender_id', $currentUserId)
                                ->orWhere('receiver_id', $currentUserId);
                        })
                        ->groupBy(DB::raw('CASE WHEN sender_id = ' . $currentUserId . ' THEN receiver_id ELSE sender_id END'));
                })
                ->with(['sender:id,name', 'receiver:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->sendResponse($lastMessages, 'Last messages fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error getting last messages', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to get last messages.', [], 500);
        }
    }

    /**
     * Delete message (JSON)
     */
    public function deleteMessage($message_id)
    {
        try {
            $message = Usertoagentchat::find($message_id);

            if (!$message) {
                return $this->sendError('Message not found.', [], 404);
            }

            if ($message->sender_id != Auth::id()) {
                return $this->sendError('You can only delete your own messages.', [], 403);
            }

            if ($message->photo && file_exists(public_path($message->photo))) {
                unlink(public_path($message->photo));
            }

            $message->delete();

            return $this->sendResponse([], 'Message deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting message', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to delete message.', [], 500);
        }
    }
}
