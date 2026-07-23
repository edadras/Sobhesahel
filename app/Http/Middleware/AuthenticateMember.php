<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * احراز هویت عضو با توکن Sanctum روی مدل Member.
 *
 * The member area is a pure token API (no session): the bearer token is
 * resolved to its personal_access_tokens row, and the tokenable Member is
 * bound onto the request (and the `member` guard) so controllers read it via
 * $request->user() and revoke the current token via
 * $member->currentAccessToken().
 *
 * Never throws — a missing/invalid/expired token or a deactivated member all
 * yield a clean 401 JSON envelope the Flutter client understands.
 */
class AuthenticateMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! is_string($bearer) || $bearer === '') {
            return $this->unauthenticated();
        }

        try {
            $accessToken = PersonalAccessToken::findToken($bearer);
        } catch (\Throwable) {
            $accessToken = null;
        }

        if ($accessToken === null) {
            return $this->unauthenticated();
        }

        // Honour Sanctum token expiration if configured.
        $expiration = config('sanctum.expiration');

        if ($expiration !== null
            && $accessToken->created_at !== null
            && $accessToken->created_at->lte(now()->subMinutes((int) $expiration))) {
            return $this->unauthenticated();
        }

        $member = $accessToken->tokenable;

        if (! $member instanceof Member || $member->is_active === false) {
            return $this->unauthenticated();
        }

        try {
            $accessToken->forceFill(['last_used_at' => now()])->save();
        } catch (\Throwable) {
            // Updating the usage timestamp must never break the request.
        }

        // Make the token retrievable for logout (revoke current token).
        $member->withAccessToken($accessToken);

        $request->setUserResolver(fn () => $member);

        try {
            Auth::guard('member')->setUser($member);
        } catch (\Throwable) {
            // The session guard may be unavailable in some contexts — ignore.
        }

        return $next($request);
    }

    protected function unauthenticated(): Response
    {
        return response()->json([
            'message' => 'برای دسترسی به این بخش باید وارد حساب کاربری خود شوید.',
        ], 401);
    }
}
