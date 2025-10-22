<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatRequestController extends Controller
{
    public function index()
    {
        // Default এ কিছু show করবে না
        $users = collect(); // Empty collection
        return view('frontend.frontendpages.friend_request', compact('users'));
    }

    // AJAX search
    public function search(Request $request)
    {
        $query = $request->get('query');

        if ($query) {
            $users = User::where('name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%")
                        ->get();
        } else {
            $users = collect(); // কিছুই না পাঠানো হলে খালি data যাবে
        }

        // HTML return (partial)
        $html = '';
        if ($users->count() > 0) {
            foreach ($users as $user) {
                $photo = $user->photo ?? 'https://via.placeholder.com/150';
                $html .= '
                <div class="friendrequest-card">
                    <img src="'.$photo.'" alt="'.$user->name.'" class="friendrequest-image">
                    <div class="friendrequest-info">
                        <div class="friendrequest-name">'.$user->name.'</div>
                        <div class="friendrequest-email text-muted">'.$user->email.'</div>
                        <div class="friendrequest-button-group">
                            <button class="friendrequest-btn friendrequest-btn-confirm">Confirm</button>
                            <button class="friendrequest-btn friendrequest-btn-delete">Delete</button>
                        </div>
                    </div>
                </div>';
            }
        } else {
            $html = '<p class="text-center text-muted mt-3">No users found.</p>';
        }

        return response($html);
    }

public function sendFriendRequest(Request $request)
{
    $receiver_id = $request->receiver_id;
    $sender_id = Auth::id();

    $exists = ChatRequest::where('sender_id', $sender_id)
                        ->where('receiver_id', $receiver_id)
                        ->first();

    if ($exists) {
        return response()->json(['message' => 'Friend request already sent.'], 409);
    }

    ChatRequest::create([
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'status' => 'pending',
    ]);

    return response()->json(['message' => 'Friend request sent.']);
}

// Cancel friend request
public function cancelFriendRequest(Request $request)
{
    $receiver_id = $request->receiver_id;
    $sender_id = Auth::id();

    $requestItem = ChatRequest::where('sender_id', $sender_id)
                              ->where('receiver_id', $receiver_id)
                              ->first();

    if ($requestItem) {
        $requestItem->delete();
        return response()->json(['message' => 'Friend request cancelled.']);
    }

    return response()->json(['message' => 'No friend request found.'], 404);
}
public function sendFriendRequestaccept()
    {
        $userId = auth()->id();

        // যেসব ইউজার তোমাকে রিকোয়েস্ট পাঠিয়েছে (তুমি receiver)
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
                            'mutual_friends' => null, // চাইলে calculate করতে পারো
                        ];
                    });

        return view('frontend.frontendpages.friend_request_accept', compact('requests'));
    }

    public function acceptRequest(Request $request)
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

    public function rejectRequest(Request $request)
    {
        ChatRequest::where('sender_id', $request->sender_id)
            ->where('receiver_id', auth()->id())
            ->delete();

        return response()->json(['message' => '❌ Friend request rejected.']);
    }

}
