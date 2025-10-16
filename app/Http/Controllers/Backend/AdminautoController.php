<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminautoController extends Controller
{
    public function admin_login(){
         return view('admin.login.login');
    }


public function admin_submit(Request $request)
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

        if ($user->role !== 'is_admin') {
            return redirect()->back()->with('error', 'User is not an admin!');
        }

        // Login existing admin
        Auth::login($user);
        return redirect()->route('admin.dashboard')->with('success', 'Admin logged in successfully!');
    }

}

public function admin_logout()
{
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login')->with('success', 'Admin logged out successfully!');
}

}
