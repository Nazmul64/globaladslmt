<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  public function handle(Request $request, Closure $next)
{
    // If not logged in or not a 'user', redirect to login
    if (!auth()->check() || auth()->user()->role !== 'user') {
        return redirect()->route('user.login');
    }

    // If user is blocked, log them out and redirect with error
   if (auth()->check() && auth()->user()->is_blocked) {
    auth()->logout();
    return redirect()->route('user.login')->withErrors('Your account is blocked.');
}

    return $next($request);
}

}
