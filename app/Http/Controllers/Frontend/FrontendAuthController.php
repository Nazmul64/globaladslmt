<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FrontendAuthController extends Controller
{
    public function user_login(){
         return view('frontend.auth.login');
    }
    public  function user_register(){
        return view('frontend.auth.register');
    }

public function user_register_submit(Request $request)
{
    // Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'mobile' => 'required|string|max:100',
        'password' => 'required|string|min:6|confirmed', // checks password_confirmation
    ]);

    // Create user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'mobile' => $request->mobile,
        'ref_code' => $request->ref_code ?? null,
        'password' => Hash::make($request->password),
        'role' => 'user',
    ]);

    // Redirect with success message
    return redirect()->route('user.login')->with('success', 'Registration successful! Please login.');
}


    public function user_submit(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    // Check if user exists
    $user =User::where('email', $request->email)->first();

    if ($user) {
        // User exists, check password and role
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password!');
        }

        if ($user->role !== 'user') {
            return redirect()->back()->with('error', 'User is not an User!');
        }

        // Login existing admin
        Auth::login($user);
        return redirect()->route('frontend.index')->with('success', 'User logged in successfully!');
    }


}
public function user_logout()
{
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('user.login')->with('success', 'user logged out successfully!');
}
}
