<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

ini_set('memory_limit', '512M');

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([ 
//            \Clockwork\Support\Laravel\ClockworkMiddleware::class,
            \App\Http\Middleware\RedirectWwwToNonWww::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
