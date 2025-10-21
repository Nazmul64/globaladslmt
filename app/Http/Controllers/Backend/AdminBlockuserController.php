<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBlockuserController extends Controller
{
 public function admin_block_user(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action'  => 'required|in:block,unblock',
        ]);

        $user = User::findOrFail($request->user_id);

        $user->is_blocked = $request->action === 'block';
        $user->save();

        return redirect()->back()->with('success', "User has been {$request->action}ed successfully.");
    }

}
