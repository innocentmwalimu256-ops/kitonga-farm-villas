<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/analytics/*',
            'api/analytics/pageview',
            'api/analytics/track',
            'api/analytics/heartbeat',
            'api/analytics/event',
            'meet-mr-kitonga/*/track-click',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
