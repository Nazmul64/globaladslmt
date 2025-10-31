<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);

    //     if(!Auth::check()  || Auth::user()->role != 'agent'){
    //         return redirect()->route('agent.login');
    //     }
    // }

    public function handle(Request $request, Closure $next): Response
{
    if (!Auth::check() || Auth::user()->role != 'agent') {
        return redirect()->route('agent.login');
    }

    return $next($request);
}

}
