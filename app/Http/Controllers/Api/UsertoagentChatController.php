<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Usertoagentchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsertoagentChatController extends BaseController
{
    /**
     * Get First Available Agent
     */
    private function getAvailableAgent()
    {
        $agent = User::where('role', 'agent')
            ->where('status', 'approved')
            ->first();

        if (!$agent) {
            $agent = User::where('role', 'agent')->first();
        }

        return $agent;
    }

    /**
     * Send Message (Text / Image)
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message'       => 'nullable|string|max:1000',
                'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'message_type'  => 'nullable|in:text,image',
            ]);

            $userId = Auth::id();
            $user   = Auth::user();

            if (!$userId) { return response()->json(['success' => false, 'message' => 'Unauthorized'], 401); }
            if ($user->role !== 'user') {
                return response()->json(['success' => false, 'message' => 'Only users can send messages'], 403);
            }

            $agent = $this->getAvailableAgent();
            if (!$agent) {
                return response()->json(['success' => false, 'message' => 'No agent available'], 404);
            }

            if (empty($request->message) && !$request->hasFile('image')) {
                return response()->json(['success' => false, 'message' => 'Either message or image required'], 422);
            }

            $imagePath   = null;
            $messageType = $request->input('message_type', 'text');

            // ========== UPLOAD CHAT IMAGE → uploads/chat ==========
            if ($request->hasFile('image')) {
                $image      = $request->file('image');
                $imageName  = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Move to public/uploads/chat
                $image->move(public_path('uploads/chat'), $imageName);

                $imagePath  = 'uploads/chat/' . $imageName;
                $messageType = 'image';
            }

            $chat = Usertoagentchat::create([
                'sender_id'    => $userId,
                'receiver_id'  => $agent->id,
                'message'      => $request->message ?? '',
                'image'        => $imagePath,
                'message_type' => $messageType,
                'is_read'      => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'মেসেজ সফলভাবে পাঠানো হয়েছে',
                'data'    => [
                    'id'          => $chat->id,
                    'sender_id'   => $chat->sender_id,
                    'sender_type' => 'user',
                    'receiver_id' => $chat->receiver_id,
                    'message'     => $chat->message ?? '',
                    'message_type'=> $chat->message_type,
                    'image_url'   => $imagePath ? asset($imagePath) : null,
                    'is_read'     => (bool) $chat->is_read,
                    'created_at'  => $chat->created_at->toIso8601String(),
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Send message error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }


    /**
     * Fetch Messages
     */
    public function fetchMessages(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$userId) { return response()->json(['success' => false, 'message' => 'Unauthorized'], 401); }
            if ($user->role !== 'user') { return response()->json(['success' => false, 'message' => 'Only users allowed'], 403); }

            $agent = $this->getAvailableAgent();
            if (!$agent) { return response()->json(['success' => false, 'message' => 'No agent available'], 404); }

            $agentId = $agent->id;

            $perPage = min(max((int)$request->input('per_page', 50), 1), 100);
            $page    = max((int)$request->input('page', 1), 1);

            $messages = Usertoagentchat::where(function ($q) use ($agentId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $agentId);
            })->orWhere(function ($q) use ($agentId, $userId) {
                $q->where('sender_id', $agentId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

            $transformed = $messages->map(function ($msg) use ($agentId) {
                return [
                    'id'          => $msg->id,
                    'sender_id'   => $msg->sender_id,
                    'sender_type' => $msg->sender_id == $agentId ? 'agent' : 'user',
                    'receiver_id' => $msg->receiver_id,
                    'message'     => $msg->message ?? '',
                    'message_type'=> $msg->message_type ?? 'text',
                    'image_url'   => $msg->image ? asset($msg->image) : null,
                    'is_read'     => (bool) $msg->is_read,
                    'created_at'  => $msg->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'মেসেজ সফলভাবে লোড হয়েছে',
                'data'    => [
                    'messages'   => $transformed,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page'    => $messages->lastPage(),
                        'per_page'     => $messages->perPage(),
                        'total'        => $messages->total(),
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Fetch error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch messages'], 500);
        }
    }


    /**
     * Mark Agent → User Messages as Read
     */
    public function markAsRead()
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$userId) { return response()->json(['success' => false, 'message' => 'Unauthorized'], 401); }
            if ($user->role !== 'user') { return response()->json(['success' => false, 'message' => 'Only users'], 403); }

            $agent = $this->getAvailableAgent();
            if (!$agent) { return response()->json(['success' => false, 'message' => 'No agent'], 404); }

            $updated = Usertoagentchat::where('sender_id', $agent->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'মেসেজ পড়া হয়েছে হিসেবে চিহ্নিত',
                'data'    => ['updated_count' => $updated]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }


    /**
     * Delete Own Message
     */
    public function deleteMessage($messageId)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$userId) { return response()->json(['success' => false, 'message' => 'Unauthorized'], 401); }
            if ($user->role !== 'user') { return response()->json(['success' => false, 'message' => 'Only users'], 403); }

            $message = Usertoagentchat::find($messageId);
            if (!$message) { return response()->json(['success' => false, 'message' => 'Message not found'], 404); }

            if ($message->sender_id != $userId) {
                return response()->json(['success' => false, 'message' => 'You cannot delete this message'], 403);
            }

            // Delete image from uploads/chat
            if ($message->image && file_exists(public_path($message->image))) {
                unlink(public_path($message->image));
            }

            $message->delete();

            return response()->json(['success' => true, 'message' => 'মেসেজ সফলভাবে মুছে ফেলা হয়েছে']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete message'], 500);
        }
    }


    /**
     * Get Agent Info
     */
    public function getAgentInfo()
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$userId) { return response()->json(['success' => false, 'message' => 'Unauthorized'], 401); }
            if ($user->role !== 'user') { return response()->json(['success' => false, 'message' => 'Only users'], 403); }

            $agent = $this->getAvailableAgent();
            if (!$agent) { return response()->json(['success' => false, 'message' => 'এজেন্ট পাওয়া যায়নি'], 404); }

            // Agent avatar → uploads/profile/
            $avatar = null;
            if ($agent->photo) {
                $avatar = asset('uploads/profile/' . basename($agent->photo));
            }

            $unreadCount = Usertoagentchat::where('sender_id', $agent->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'এজেন্ট তথ্য লোড হয়েছে',
                'data'    => [
                    'id'           => $agent->id,
                    'name'         => $agent->name ?? 'Support Agent',
                    'email'        => $agent->email,
                    'avatar'       => $avatar,
                    'status'       => $agent->status ?? 'available',
                    'unread_count' => $unreadCount,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get agent info'], 500);
        }
    }


    /**
     * Get Unread Count
     */
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            if (!$userId) { return response()->json(['success' => false, 'message' => 'Unauthorized'], 401); }
            if ($user->role !== 'user') { return response()->json(['success' => false, 'message' => 'Only users'], 403); }

            $agent = $this->getAvailableAgent();
            if (!$agent) { return response()->json(['success' => false, 'message' => 'No agent available'], 404); }

            $count = Usertoagentchat::where('sender_id', $agent->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Unread count retrieved',
                'data'    => ['unread_count' => $count]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get unread count'], 500);
        }
    }
}
