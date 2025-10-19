<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RefferCommissionSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FrontendAuthController extends Controller
{
    /**
     * Show user login page
     */
    public function user_login()
    {
        return view('frontend.auth.login');
    }

    /**
     * Show user register page
     */
    public function user_register()
    {
        return view('frontend.auth.register');
    }

    /**
     * Handle user registration
     */
    public function user_register_submit(Request $request)
    {
        // ✅ Validation
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|string|max:100',
            'password' => 'required|string|min:6|confirmed',
            'ref_code' => 'nullable|string|exists:users,ref_code',
        ]);

        // ✅ Check referral
        $referredBy = null;
        $referrer   = null;

        if ($request->ref_code) {
            $referrer = User::where('ref_code', $request->ref_code)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        // ✅ Generate unique 8-digit numeric referral code
        do {
            $refCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::where('ref_code', $refCode)->exists());

        // ✅ Create new user
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'mobile'      => $request->mobile,
            'password'    => Hash::make($request->password),
            'role'        => 'user',
            'referred_by' => $referredBy,
            'ref_code'    => $refCode,
            'balance'     => 0,
            'refer_income' => 0,
            'generation_income' => 0,
        ]);

        // ✅ Distribute referral commission
        if ($referrer) {
            $this->distributeReferralCommission($user, $referrer);
        }

        return redirect()->route('frontend.index')->with('success', 'Registration successful! Please login.');
    }

    /**
     * 💸 Commission distribution logic
     */
    private function distributeReferralCommission(User $newUser, User $referrer)
    {
        $commissionLevels = RefferCommissionSetup::all();
        $upline = $referrer;
        $level = 1;

        foreach ($commissionLevels as $commission) {
            if (!$upline) break;

            // Example: Assume new user spent $100 (you can replace with real value later)
            $amount = 100;
            $percentage = $commission->commission_percentage;
            $commissionAmount = ($percentage / 100) * $amount;

            // Add commission to upline user
            $upline->generation_income += $commissionAmount;
            $upline->save();

            // Move to next level referrer
            $upline = $upline->referrer;
            $level++;
        }
    }

    /**
     * Handle user login
     */
    public function user_submit(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'No user found with this email!');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password!');
        }

        if ($user->role !== 'user') {
            return back()->with('error', 'Invalid user role!');
        }

        Auth::login($user);
        return redirect()->route('frontend.index')->with('success', 'User logged in successfully!');
    }

    /**
     * Handle user logout
     */
    public function user_logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('user.login')->with('success', 'User logged out successfully!');
    }
}
