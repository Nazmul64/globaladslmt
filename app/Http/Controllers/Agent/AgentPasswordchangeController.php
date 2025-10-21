<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AgentPasswordchangeController extends Controller
{
    public function agent_password_change(){
        return view('agent.password.index');
    }

 public function agent_password_submit(Request $request)
    {
        $user = Auth::user();

        // Ensure user role is 'agent'
        if ($user->role !== 'agent') {
            return redirect()->back()->with('error', 'You are not authorized to change this password.');
        }

        // Validate the request
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed', // expects new_password_confirmation
        ]);

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'Current password does not match.');
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
}
