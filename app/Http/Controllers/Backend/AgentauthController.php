<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentauthController extends Controller
{
    /* =========================
        LOGIN & REGISTER PAGES
    ========================== */

    public function agent_login()
    {
        return view('agent.login.login');
    }

    public function agent_register()
    {
        return view('agent.login.register');
    }

    /* =========================
        AGENT REGISTER SUBMIT
    ========================== */

    public function agent_register_submit(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'mobile'   => 'required|string|max:20',
            'country'  => 'required|string|max:100',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'country'  => $request->country,
            'password' => Hash::make($request->password),
            'role'     => 'agent',
            'status'   => 'pending', // 🔴 admin approval needed
        ]);

        return redirect()->back()
            ->with('success', 'Registration successful! Please wait for admin approval.');
    }

    /* =========================
        AGENT LOGIN SUBMIT
    ========================== */

    public function agent_submit(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        // ❌ user not found
        if (!$user) {
            return redirect()->back()->with('error', 'Account not found!');
        }

        // ❌ password mismatch
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password!');
        }

        // ❌ not an agent
        if ($user->role !== 'agent') {
            return redirect()->back()->with('error', 'User is not an Agent!');
        }

        // 🔥 MAIN RULE: ADMIN APPROVAL CHECK
        if ($user->status !== 'approved') {
            return redirect()->back()->with('error', 'Please wait for admin approval!');
        }

        // ✅ approved agent login
        Auth::login($user);

        return redirect()->route('agent.dashboard')
            ->with('success', 'Agent logged in successfully!');
    }

    /* =========================
        AGENT LOGOUT
    ========================== */

    public function agent_logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('agent.login')
            ->with('success', 'Agent logged out successfully!');
    }
}
