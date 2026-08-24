<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoadminchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UsertoadminchatController extends Controller
{
    protected function getAdminId()
    {
        $admin = User::where('role', 'is_admin')
            ->orWhere('role', 'admin')
            ->orWhere('email', 'admin@gmail.com')
            ->orWhere('name', 'like', '%Admin%')
            ->first();

        return $admin ? $admin->id : 3;
    }

    /**
     * Send Message (AJAX)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'receiver_id' => 'nullable|integer',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
        }

        $receiverId = $request->receiver_id ?? $this->getAdminId();

        $chat = Usertoadminchat::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'chat' => $chat,
            'time' => $chat->created_at->format('h:i A'),
            'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
        ]);
    }

    /**
     * Fetch Messages (AJAX)
     */
    public function fetchMessages()
    {
        $adminId = $this->getAdminId();
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([]);
        }

        $messages = Usertoadminchat::where(function ($query) use ($adminId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $adminId);
        })->orWhere(function ($query) use ($adminId, $userId) {
            $query->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    // Mark messages as read
    public function markRead(Request $request)
    {
        $userId = Auth::id();
        $senderId = $request->sender_id ?? $this->getAdminId();

        Usertoadminchat::where('sender_id', $senderId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}