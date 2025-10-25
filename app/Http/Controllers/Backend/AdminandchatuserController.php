<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoadminchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminandchatuserController extends Controller
{
    // ✅ ইউজার লিস্ট (শুধু role=user)
    public function adminuserchat()
    {
        $users = User::where('role', 'user')
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.userchat.index', compact('users'));
    }

    // ✅ চ্যাট লোড
    public function fetchMessages($user_id)
    {
        $adminId = Auth::id();

        $messages = Usertoadminchat::where(function ($q) use ($user_id, $adminId) {
            $q->where('sender_id', $user_id)->where('receiver_id', $adminId);
        })
        ->orWhere(function ($q) use ($user_id, $adminId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $user_id);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        // unread message → read করে দেওয়া
        Usertoadminchat::where('sender_id', $user_id)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // ✅ মেসেজ পাঠানো
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer',
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
        }

        $chat = Usertoadminchat::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'chat' => $chat,
            'time' => $chat->created_at->format('h:i A'),
        ]);
    }
    // AdminandchatuserController.php
public function unreadCount(){
    $adminId = Auth::id();
    $counts = Usertoadminchat::where('receiver_id',$adminId)
                ->where('is_read', false)
                ->groupBy('sender_id')
                ->selectRaw('sender_id, COUNT(*) as count')
                ->pluck('count','sender_id');
    return response()->json($counts);
}
public function markRead($user_id){
    $adminId = Auth::id();
    Usertoadminchat::where('sender_id',$user_id)
        ->where('receiver_id',$adminId)
        ->update(['is_read'=>true]);
    return response()->json(['success'=>true]);
}

}
