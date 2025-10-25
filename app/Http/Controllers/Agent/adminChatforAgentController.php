<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Adminchatforagent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminChatForAgentController extends Controller
{
    /**
     * 🏠 এজেন্ট চ্যাট পেজ
     */
    public function index()
    {
        // ধরে নিচ্ছি একটিমাত্র এডমিন আছে
        $admin = User::where('role', 'is_admin')->first();

        return view('agent.chatforadmin.index', compact('admin'));
    }

    /**
     * 📩 মেসেজ পাঠানো
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = User::where('role', 'is_admin')->first();
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        $chat = new Adminchatforagent();
        $chat->sender_id = Auth::id();
        $chat->receiver_id = $admin->id;
        $chat->message = $request->message ?? '';
        $chat->is_read = false;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/chat', 'public');
            $chat->image = 'storage/' . $path;
        }

        $chat->save();

        return response()->json(['success' => true, 'chat' => $chat]);
    }

    /**
     * 📥 সব মেসেজ
     */
    public function fetchMessages()
    {
        $admin = User::where('role', 'is_admin')->first();
        if (!$admin) return response()->json([]);

        $messages = Adminchatforagent::where(function ($q) use ($admin) {
            $q->where('sender_id', Auth::id())
              ->where('receiver_id', $admin->id);
        })->orWhere(function ($q) use ($admin) {
            $q->where('sender_id', $admin->id)
              ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    /**
     * ✅ মেসেজ পড়া
     */
    public function markRead()
    {
        $admin = User::where('role', 'is_admin')->first();
        if ($admin) {
            Adminchatforagent::where('receiver_id', Auth::id())
                ->where('sender_id', $admin->id)
                ->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    }
}
