<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminuseraccountviewController extends Controller
{
    public function impersonateUser($userId)
    {
        $user = User::findOrFail($userId);

        // Only admin can impersonate
        if (auth()->user()->role !== 'is_admin') {
            abort(403, 'Unauthorized');
        }

        // Save admin session
        session([
            'impersonate' => $user->id,
            'impersonate_admin_id' => auth()->id()
        ]);

        Auth::login($user);

        return redirect()
            ->route('frontend.index')
            ->with('success', 'Now you are logged in as ' . $user->name);
    }

    public function stopImpersonate()
    {
        $adminId = session('impersonate_admin_id');

        if (!$adminId) {
            abort(403, 'Admin Session Missing');
        }

        Auth::logout();

        session()->forget(['impersonate', 'impersonate_admin_id']);

        $admin = User::findOrFail($adminId);
        Auth::login($admin);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'You are now back as Admin');
    }
}
