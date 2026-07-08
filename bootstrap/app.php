<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->append(\App\Http\Middleware\BlockDangerousFiles::class);
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->alias([
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'iot.api_key' => \App\Http\Middleware\IotApiKeyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
