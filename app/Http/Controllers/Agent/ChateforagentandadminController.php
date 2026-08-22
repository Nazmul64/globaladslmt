<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Adminchatforagent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

class ChateforagentandadminController extends Controller
{
    // Show agent list to admin
    public function agent_for_chat_admin()
    {
        $adminId = Auth::id();

        $agents = User::where('role', 'agent')
            ->where('status', 'approved') // ✅ ONLY APPROVED AGENTS
            ->select('id', 'name', 'email', 'photo')
            ->get()
            ->map(function ($agent) use ($adminId) {
                $latestMsg = Adminchatforagent::where(function ($q) use ($agent, $adminId) {
                        $q->where('sender_id', $agent->id)->where('receiver_id', $adminId);
                    })
                    ->orWhere(function ($q) use ($agent, $adminId) {
                        $q->where('sender_id', $adminId)->where('receiver_id', $agent->id);
                    })
                    ->latest()
                    ->first();

                $unreadCount = Adminchatforagent::where('sender_id', $agent->id)
                    ->where('receiver_id', $adminId)
                    ->where('is_read', 0)
                    ->count();

                $agent->latest_message_time = $latestMsg ? $latestMsg->created_at->timestamp : 0;
                $agent->latest_message_id = $latestMsg ? $latestMsg->id : 0;
                $agent->last_message_text = $latestMsg ? ($latestMsg->message ? Str::limit($latestMsg->message, 30) : ($latestMsg->image ? '📷 Image' : '')) : '';
                $agent->unread_count = $unreadCount;
                return $agent;
            })
            ->sortByDesc('latest_message_time')
            ->values();

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

        $chat = Adminchatforagent::create($data);

        return response()->json([
            'success' => true,
            'chat' => $chat
        ]);
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

    // Get all unread counts and latest activity timestamps
    public function allUnreadCount()
    {
        $adminId = Auth::id();

        $unreadCounts = Adminchatforagent::where('receiver_id', $adminId)
            ->where('is_read', 0)
            ->groupBy('sender_id')
            ->selectRaw('sender_id, COUNT(*) as count')
            ->pluck('count', 'sender_id');

        $latestMessages = Adminchatforagent::where('receiver_id', $adminId)
            ->orWhere('sender_id', $adminId)
            ->latest()
            ->get()
            ->groupBy(function($msg) use ($adminId) {
                return $msg->sender_id == $adminId ? $msg->receiver_id : $msg->sender_id;
            })
            ->map(function($group) {
                $latest = $group->first();
                return [
                    'last_time' => $latest->created_at->timestamp,
                    'last_message' => $latest->message ? Str::limit($latest->message, 30) : ($latest->image ? '📷 Image' : ''),
                    'last_id' => $latest->id,
                ];
            });

        return response()->json([
            'unread' => $unreadCounts,
            'latest' => $latestMessages,
        ]);
    }

}
