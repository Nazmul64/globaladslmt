<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoagentchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentchattouserChatController extends Controller
{
    // Agent panel: সব ইউজার দেখাও (যাতে যেকোনো user-এর সাথে chat করতে পারে)
    public function index()
    {
        $agentId = Auth::id();

        // সব user যাদের role 'user' (agent নিজে বাদে)
        $users = User::where('role', 'user')
                    ->where('id', '!=', $agentId)
                    ->orderBy('name', 'asc')
                    ->get();

        return view('agent.usertoagentchat.index', compact('users'));
    }

    // Send message
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $chat = new Usertoagentchat();
        $chat->sender_id = Auth::id();
        $chat->receiver_id = $request->receiver_id;
        $chat->message = $request->message;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/chat'), $imageName);
            $chat->image = 'uploads/chat/' . $imageName;
        }

        $chat->save();

        return response()->json(['success' => true, 'message' => 'Message sent successfully']);
    }

    // Load chat messages
    public function messages(Request $request)
    {
        $agentId = Auth::id();
        $receiverId = $request->receiver_id;

        $messages = Usertoagentchat::where(function($q) use ($agentId, $receiverId){
                $q->where('sender_id', $agentId)->where('receiver_id', $receiverId);
            })
            ->orWhere(function($q) use ($agentId, $receiverId){
                $q->where('sender_id', $receiverId)->where('receiver_id', $agentId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Usertoagentchat::where('receiver_id', $agentId)
            ->where('sender_id', $receiverId)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // Unread counts per user
    public function unreadCounts()
    {
        $agentId = Auth::id();

        $counts = Usertoagentchat::where('receiver_id', $agentId)
                    ->where('is_read', false)
                    ->selectRaw('sender_id, COUNT(*) as count')
                    ->groupBy('sender_id')
                    ->pluck('count', 'sender_id');

        return response()->json($counts);
    }
}
