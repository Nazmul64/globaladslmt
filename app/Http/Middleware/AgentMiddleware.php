<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentMiddleware
{
    /**
     * Handle an incoming request.
     */
   public function handle(Request $request, Closure $next)
{
    // If user is logged in → update last_active_at
    if (Auth::check()) {
        Auth::user()->update([
            'last_active_at' => now(),
        ]);
    }

    // Allow if:
    // - Auth user is Agent OR
    // - Admin is impersonating an agent
    if (
        Auth::check() && (
            Auth::user()->role === 'agent' ||
            session()->has('impersonate_admin_id')
        )
    ) {
        return $next($request);
    }

    // Unauthorized → logout & redirect
    if (Auth::check()) {
        Auth::logout();
    }

    return redirect()
        ->route('agent.login')
        ->withErrors(['error' => 'Unauthorized access.']);
}

}
