<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isblocked) {
            // Allow access to blocked and logout routes
            if ($request->routeIs('blocked') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Redirect blocked users to blocked page
            return redirect()->route('blocked');
        }

        return $next($request);
    }
}
