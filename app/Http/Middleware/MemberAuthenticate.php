<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the member panel (member/... pages): only the `member` guard counts —
 * an authenticated admin (web guard) is still a guest here. Guests are sent to
 * the member login page with their intended URL remembered.
 */
class MemberAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('member')->check()) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }

            return redirect()->guest(route('member.login'));
        }

        // A deactivated member loses access immediately.
        $member = Auth::guard('member')->user();

        if ($member && $member->is_active === false) {
            Auth::guard('member')->logout();

            return redirect()->route('member.login')
                ->withErrors(['mobile' => 'حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.']);
        }

        return $next($request);
    }
}
