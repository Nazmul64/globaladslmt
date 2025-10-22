<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserchatController extends Controller
{
    /**
     * ✅ Show friend list (accepted requests)
     */
    public function frontend_chat_list()
    {
        $userId = Auth::id();

        $friends = ChatRequest::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })
        ->where('status', 'accepted')
        ->with(['sender', 'receiver'])
        ->get();

        $contacts = $friends->map(function ($item) use ($userId) {
            return $item->sender_id == $userId ? $item->receiver : $item->sender;
        });

        return view('frontend.userchat.index', compact('contacts'));
    }

    /**
     * ✅ Send message (text or image)
     */
    public function frontend_chat_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_without:image|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $senderId = Auth::id();
        $receiverId = $request->receiver_id;

        // Check friendship
        $areFriends = ChatRequest::where(function ($q) use ($senderId, $receiverId) {
            $q->where(function ($sub) use ($senderId, $receiverId) {
                $sub->where('sender_id', $senderId)->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($sub) use ($senderId, $receiverId) {
                $sub->where('sender_id', $receiverId)->where('receiver_id', $senderId);
            });
        })
        ->where('status', 'accepted')
        ->exists();

        if (!$areFriends) {
            return response()->json([
                'success' => false,
                'message' => 'You are not friends with this user.'
            ], 403);
        }

        // Create message
        $chatMessage = new ChatMessage();
        $chatMessage->sender_id = $senderId;
        $chatMessage->receiver_id = $receiverId;
        $chatMessage->message = $request->message;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/chat'), $imageName);
            $chatMessage->image = $imageName;
        }

        $chatMessage->is_read = false;
        $chatMessage->save();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $chatMessage->id,
                'message' => $chatMessage->message,
                'image' => $chatMessage->image ? asset('uploads/chat/' . $chatMessage->image) : null,
                'created_at' => $chatMessage->created_at->format('h:i A'),
            ]
        ]);
    }

    /**
     * ✅ Fetch messages between two users
     */
    public function frontend_chat_messages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $authId = Auth::id();
        $userId = $request->user_id;

        $messages = ChatMessage::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)
              ->where('receiver_id', $userId)
              ->orWhere(function ($sub) use ($authId, $userId) {
                  $sub->where('sender_id', $userId)->where('receiver_id', $authId);
              });
        })
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark messages as read
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $formatted = $messages->map(function ($msg) use ($authId) {
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'image' => $msg->image ? asset('uploads/chat/' . $msg->image) : null,
                'is_sent' => $msg->sender_id == $authId,
                'created_at' => $msg->created_at->format('h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    /**
     * ✅ Get unread message counts per sender
     */
    public function getUnreadCounts()
    {
        $userId = Auth::id();

        $counts = ChatMessage::select('sender_id', DB::raw('COUNT(*) as unread_count'))
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->pluck('unread_count', 'sender_id');

        return response()->json([
            'success' => true,
            'counts' => $counts
        ]);
    }
}
