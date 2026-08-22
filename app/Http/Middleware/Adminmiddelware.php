<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Adminmiddelware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow if Admin or impersonating
        if (Auth::check() && (
            Auth::user()->role === 'is_admin' ||
            session()->has('impersonate_admin_id')
        )) {
            return $next($request);
        }

        // Not admin → logout
        if (Auth::check()) {
            Auth::logout();
        }

        return redirect()
            ->route('admin.login')
            ->withErrors(['error' => 'Unauthorized access.']);
    }
}
