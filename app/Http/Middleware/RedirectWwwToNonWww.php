<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWwwToNonWww
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() === 'www.sobhesahel.com') {
            return redirect()->to('https://sobhesahel.com' . $request->getRequestUri(), 301);
        }

        if ($request->getHost() === 'www.sobhesahel.ir') {
            return redirect()->to('https://sobhesahel.com' . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
