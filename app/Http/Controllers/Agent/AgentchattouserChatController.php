<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoagentchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentchattouserChatController extends Controller
{
    // Show chat page
    public function index()
    {
        $agentId = Auth::id();

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
        $chat->is_read = false;

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/chat'), $imageName);
            $chat->image = 'uploads/chat/' . $imageName;
        }

        $chat->save();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $chat
        ]);
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

    // Mark messages as read (NEW METHOD)
    public function markAsRead(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id'
        ]);

        $agentId = Auth::id();

        Usertoagentchat::where('receiver_id', $agentId)
            ->where('sender_id', $request->sender_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // Check for new messages (NEW METHOD)
    public function checkNewMessages()
    {
        $agentId = Auth::id();

        // Get latest unread message from each sender
        $newMessages = Usertoagentchat::where('receiver_id', $agentId)
                              ->where('is_read', false)
                              ->with('sender:id,name,photo')
                              ->orderBy('created_at', 'desc')
                              ->get()
                              ->unique('sender_id')
                              ->map(function($message) {
                                  return [
                                      'id' => $message->id,
                                      'sender_id' => $message->sender_id,
                                      'sender_name' => $message->sender->name,
                                      'sender_photo' => $message->sender->photo
                                          ? asset('uploads/profile/' . $message->sender->photo)
                                          : 'https://i.pravatar.cc/150?img=' . rand(1,70),
                                      'message' => $message->message,
                                      'created_at' => $message->created_at
                                  ];
                              })
                              ->values();

        return response()->json($newMessages);
    }
}
