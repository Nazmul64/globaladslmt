<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
}
