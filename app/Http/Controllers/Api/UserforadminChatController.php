<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usertoadminchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserforadminChatController extends Controller
{
    /**
     * ✅ Send Message (Text / Image)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'      => 'nullable|string|max:1000',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'message_type' => 'nullable|in:text,image',
        ]);

        $adminId = 1; // Fixed Admin ID
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $imagePath = null;
        $messageType = $request->input('message_type', 'text');

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
            $messageType = 'image';
        }

        // Create message
        $chat = Usertoadminchat::create([
            'sender_id'    => $userId,
            'receiver_id'  => $adminId,
            'message'      => $request->message ?? '',
            'image'        => $imagePath,
            'message_type' => $messageType,
            'is_read'      => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'মেসেজ সফলভাবে পাঠানো হয়েছে',
            'data'    => [
                'id'           => $chat->id,
                'sender_id'    => $chat->sender_id,
                'sender_type'  => 'user',
                'receiver_id'  => $chat->receiver_id,
                'message'      => $chat->message,
                'message_type' => $chat->message_type,
                'image_url'    => $imagePath ? asset('storage/' . $imagePath) : null,
                'is_read'      => $chat->is_read,
                'created_at'   => $chat->created_at->toIso8601String(),
            ]
        ], 201);
    }

    /**
     * ✅ Fetch Chat Messages with Pagination
     */
    public function fetchMessages(Request $request)
    {
        $adminId = 1; // Fixed Admin ID
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        // Fetch messages between user and admin with pagination
        $messages = Usertoadminchat::where(function ($query) use ($adminId, $userId) {
                $query->where('sender_id', $userId)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($query) use ($adminId, $userId) {
                $query->where('sender_id', $adminId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform messages
        $transformedMessages = $messages->getCollection()->reverse()->values()->map(function ($msg) use ($userId) {
            return [
                'id'           => $msg->id,
                'sender_id'    => $msg->sender_id,
                'sender_type'  => $msg->sender_id == 1 ? 'admin' : 'user',
                'receiver_id'  => $msg->receiver_id,
                'message'      => $msg->message ?? '',
                'message_type' => $msg->message_type ?? 'text',
                'image_url'    => $msg->image ? asset('storage/' . $msg->image) : null,
                'is_read'      => (bool) $msg->is_read,
                'created_at'   => $msg->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'মেসেজ সফলভাবে লোড হয়েছে',
            'data'    => [
                'messages'   => $transformedMessages,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page'    => $messages->lastPage(),
                    'per_page'     => $messages->perPage(),
                    'total'        => $messages->total(),
                    'from'         => $messages->firstItem(),
                    'to'           => $messages->lastItem(),
                ],
            ]
        ], 200);
    }

    /**
     * ✅ Mark Messages as Read
     */
    public function markAsRead(Request $request)
    {
        $userId = Auth::id();
        $adminId = 1;

        Usertoadminchat::where('sender_id', $adminId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
        ], 200);
    }
}
