<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserfriendrequestforAgentController extends Controller
{

public function agentsendFriendRequest(Request $request)
{
    $receiver_id = $request->receiver_id;
    $sender_id = Auth::id();

    // Check if already sent
    $exists = ChatRequest::where('sender_id', $sender_id)
                        ->where('receiver_id', $receiver_id)
                        ->first();

    if ($exists) {
        return redirect()->back()->with('error', 'Friend request already sent!');
    }

    // Create new friend request
    ChatRequest::create([
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'status' => 'pending',
    ]);

    return redirect()->back()->with('success', 'Friend request sent successfully!');
}

}


