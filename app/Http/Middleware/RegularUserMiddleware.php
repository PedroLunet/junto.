<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegularUserMiddleware
{
    // handle an incoming request
    // prevent admin users from accessing regular user areas
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isadmin) {
            // redirect admin users to their dashboard
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
