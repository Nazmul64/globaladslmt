<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoadminchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminchatuserController extends Controller
{
    // ✅ ইউজার লিস্ট পেজ
    // public function adminuserchat()
    // {
    //     $users = User::select('id', 'name', 'email')->get();
    //     return view('admin.userchat.index', compact('users'));
    // }

    // ✅ ইউজার অনুযায়ী চ্যাট লোড
    // public function fetchMessages($user_id)
    // {
    //     $adminId = Auth::id();

    //     $messages = Usertoadminchat::where(function ($q) use ($user_id, $adminId) {
    //         $q->where('sender_id', $user_id)->where('receiver_id', $adminId);
    //     })->orWhere(function ($q) use ($user_id, $adminId) {
    //         $q->where('sender_id', $adminId)->where('receiver_id', $user_id);
    //     })
    //     ->orderBy('created_at', 'asc')
    //     ->get();

    //     // unread মেসেজকে read করে দেবে
    //     Usertoadminchat::where('sender_id', $user_id)
    //         ->where('receiver_id', $adminId)
    //         ->where('is_read', false)
    //         ->update(['is_read' => true]);

    //     return response()->json($messages);
    // }

    // ✅ মেসেজ পাঠানো
    // public function sendMessage(Request $request)
    // {
    //     $request->validate([
    //         'receiver_id' => 'required|integer',
    //         'message' => 'nullable|string',
    //         'image' => 'nullable|image|max:2048',
    //     ]);

    //     $imagePath = null;
    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('chat_images', 'public');
    //     }

    //     $chat = Usertoadminchat::create([
    //         'sender_id' => Auth::id(),
    //         'receiver_id' => $request->receiver_id,
    //         'message' => $request->message,
    //         'image' => $imagePath,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'chat' => $chat,
    //         'time' => $chat->created_at->format('h:i A'),
    //     ]);
    // }
}
