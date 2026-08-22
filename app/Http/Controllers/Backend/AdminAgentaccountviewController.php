<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminAgentaccountviewController extends Controller
{
    /**
     * Admin impersonates an agent
     */
    public function impersonateUser($userId)
    {
        $user = User::findOrFail($userId);

        // Only Admin can impersonate
        if (auth()->user()->role !== 'is_admin') {
            abort(403, 'Unauthorized');
        }

        // Store original admin ID in session
        session([
            'impersonate' => $user->id,
            'impersonate_admin_id' => auth()->id()
        ]);

        // Login as selected user
        Auth::login($user);

        return redirect()
            ->route('agent.dashboard')
            ->with('success', 'You are now logged in as: ' . $user->name);
    }

    /**
     * Stop impersonation & return admin back
     */
    public function stopImpersonate()
    {
        $adminId = session('impersonate_admin_id');

        if (!$adminId) {
            abort(403, 'Admin session missing');
        }

        // Logout impersonated user
        Auth::logout();

        // Remove impersonation session keys
        session()->forget(['impersonate', 'impersonate_admin_id']);

        // Login original admin
        $admin = User::findOrFail($adminId);
        Auth::login($admin);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Returned to Admin account successfully');
    }
}
