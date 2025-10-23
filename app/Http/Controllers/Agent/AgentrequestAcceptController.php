<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use Illuminate\Http\Request;

class AgentrequestAcceptController extends Controller
{

public function agentacceptRequestview()
    {
        $userId = auth()->id();

        $requests = ChatRequest::where('receiver_id', $userId)
                    ->where('status', 'pending') // pending status
                    ->with('sender') // sender relation load করবো
                    ->get()
                    ->map(function($req) {
                        return [
                            'id' => $req->sender->id,
                            'name' => $req->sender->name,
                            'email' => $req->sender->email,
                            'photo' => $req->sender->photo,
                            'mutual_friends' => null,
                        ];
                    });

        return view('agent.userrequestaccept.index', compact('requests'));
    }

   public function agentacceptRequest(Request $request)
    {
        $friendRequest = ChatRequest::where('sender_id', $request->sender_id)
                            ->where('receiver_id', auth()->id())
                            ->first();

        if ($friendRequest) {
            $friendRequest->update(['status' => 'accepted']);
            return response()->json(['message' => '✅ Friend request accepted successfully!']);
        }

        return response()->json(['message' => 'Request not found.'], 404);
    }

    public function agentrejectRequest(Request $request)
    {
        ChatRequest::where('sender_id', $request->sender_id)
            ->where('receiver_id', auth()->id())
            ->delete();

        return response()->json(['message' => '❌ Friend request rejected.']);
    }

}
