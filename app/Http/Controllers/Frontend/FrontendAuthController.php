<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FrontendAuthController extends Controller
{
    public function user_login()
    {
        return view('frontend.auth.login');
    }

    public function user_register()
    {
        return view('frontend.auth.register');
    }

    public function user_register_submit(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|string|max:100',
            'password' => 'required|string|min:6|confirmed',
            'ref_code' => 'nullable|string|exists:users,ref_code',
        ]);

        $referredBy = null;
        if ($request->ref_code) {
            $referrer = User::where('ref_code', $request->ref_code)->first();
            if ($referrer) $referredBy = $referrer->id;
        }

        do {
            $refCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::where('ref_code', $refCode)->exists());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'referred_by' => $referredBy,
            'ref_code' => $refCode,
            'balance' => 0,
            'refer_income' => 0,
            'generation_income' => 0,
        ]);

        return redirect()->route('frontend.index')->with('success', 'Registration successful! Please login.');
    }

    public function user_submit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || $user->role !== 'user') {
            return back()->with('error', 'Invalid credentials!');
        }

        Auth::login($user);
        return redirect()->route('frontend.index')->with('success', 'Logged in successfully!');
    }

    public function user_logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('user.login')->with('success', 'Logged out successfully!');
    }
}
