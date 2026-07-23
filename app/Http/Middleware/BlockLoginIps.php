<?php

namespace App\Http\Middleware;

use App\Services\LoginSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lightweight guard on the Filament panel login route: rejects requests from
 * IPs that are statically blocked (config login-security.ip.blocked_ips) or
 * temporarily auto-blocked after too many failed attempts across accounts.
 */
class BlockLoginIps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->routeIs('filament.*.auth.login')
            && app(LoginSecurityService::class)->isIpBlocked($request->ip())
        ) {
            abort(403, 'دسترسی شما به صفحه ورود به دلیل تلاش‌های ناموفق مکرر، موقتاً مسدود شده است.');
        }

        return $next($request);
    }
}
