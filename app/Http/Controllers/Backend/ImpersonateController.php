<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Admin impersonates ANY user (admin, agent, user)
     */
    public function impersonate($id)
    {
        $targetUser = User::findOrFail($id);

        // Get logged in admin
        $currentRole = strtolower(Auth::user()->role);

        // Only admin can impersonate
        if (!in_array($currentRole, ['admin', 'superadmin', 'is_admin', '1'])) {
            abort(403, 'Unauthorized');
        }

        // Store admin original session
        session([
            'impersonate_admin_id' => Auth::id(),
            'impersonate_user_id'  => $targetUser->id
        ]);

        // Login as selected user
        Auth::login($targetUser);

        // REDIRECT BASED ON ROLE
        if ($targetUser->role === 'agent') {
            return redirect()->route('agent.dashboard')
                ->with('success', 'Now impersonating Agent: ' . $targetUser->name);
        }

        if ($targetUser->role === 'user') {
            return redirect()->route('frontend.index')
                ->with('success', 'Now impersonating User: ' . $targetUser->name);
        }

        // If impersonating another admin
        return redirect()->route('admin.dashboard')
                ->with('success', 'Now impersonating Admin: ' . $targetUser->name);
    }

    /**
     * Stop impersonation
     */
    public function stop()
    {
        $adminId = session('impersonate_admin_id');

        if (!$adminId) {
            abort(403, 'Admin session missing');
        }

        // Logout impersonated user
        Auth::logout();

        // Clear impersonation session
        session()->forget(['impersonate_user_id', 'impersonate_admin_id']);

        // Login original admin
        $admin = User::findOrFail($adminId);
        Auth::login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Returned to Admin Account');
    }
}
