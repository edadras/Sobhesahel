<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Member "guest" middleware: an already-authenticated member visiting the
 * login page is sent straight to the member dashboard.
 */
class RedirectIfMemberAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('member')->check()) {
            return redirect()->route('member.dashboard');
        }

        return $next($request);
    }
}
