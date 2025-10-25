<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Adminchatforagent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChateforagentandadminController extends Controller
{
   // Show agent list to admin
    public function agent_for_chat_admin()
    {
        $agents = User::where('role', 'agent')->select('id','name','email')->get();
        return view('admin.agentchateforadmin.index', compact('agents'));
    }

    // Fetch messages between admin and selected agent
    public function fetchMessages($user_id)
    {
        $myId = Auth::id();

        $messages = Adminchatforagent::where(function($q) use ($myId, $user_id) {
                $q->where('sender_id', $myId)->where('receiver_id', $user_id);
            })
            ->orWhere(function($q) use ($myId, $user_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    // Send message (text or image)
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'receiver_id' => 'required|integer',
        ]);

        $data = [
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/chat'), $imageName);

            $data['image'] = 'uploads/chat/' . $imageName;
        }

        Adminchatforagent::create($data);

        return response()->json(['success' => true]);
    }

    // Mark messages as read
    public function markRead($user_id)
    {
        Adminchatforagent::where('sender_id', $user_id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['success' => true]);
    }

    // Get unread message count for sidebar
    public function unreadCount($agentId)
    {
        $count = Adminchatforagent::where('sender_id', $agentId)
                    ->where('receiver_id', auth()->id())
                    ->where('is_read', 0)
                    ->count();
        return response()->json($count);
    }

}
