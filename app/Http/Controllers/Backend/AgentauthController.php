<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentauthController extends Controller
{
    public function agent_login(){
         return view('agent.login.login');
    }
    public function agent_register(){
         return view('agent.login.register');
    }
public function agent_register_submit(Request $request)
{
    // Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'country' => 'required|string|max:100',
    ]);

    // Create agent
    $agent = User::create([
        'name' => $request->name,
        'country' => $request->country,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'agent',
        'status' => 'pending',
    ]);

    // Redirect to login with success toastr alert
    return redirect()->back()->with('success', 'Registration successful! Please wait for admin approval.');
}



public function agent_submit(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    // Check if user exists
    $user = User::where('email', $request->email)->first();

    if ($user) {
        // User exists, check password and role
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password!');
        }

        if ($user->role !== 'agent') {
            return redirect()->back()->with('error', 'User is not an Agent!');
        }

        // Login existing admin
        Auth::login($user);
        return redirect()->route('agent.dashboard')->with('success', 'Agent logged in successfully!');
    }

}

public function agent_logout()
{
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('agent.login')->with('success', 'Agent logged out successfully!');
}
}
