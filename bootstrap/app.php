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

        // Exclude load test endpoint from CSRF (only when APP_ENV=local)
        if (env('APP_ENV') === 'local') {
            $middleware->validateCsrfTokens(except: [
                'students/pendaftaran/daftar',
            ]);
        }

        $middleware->alias([
            'guest.student' => \App\Http\Middleware\RedirectIfAuthenticatedStudent::class,
            'admin'         => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'panitia'       => \App\Http\Middleware\EnsureUserIsPanitia::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
