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
        // Disabled statefulApi() - Not needed for mobile API with Bearer tokens
        // statefulApi() enables CSRF protection which causes 419 errors for external requests
        // $middleware->statefulApi();

        // Use custom Authenticate middleware for API
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);

        // IMPORTANT: Apply custom CORS FIRST (prepend) to ensure headers are added before any other processing
        $middleware->api(prepend: [
            \App\Http\Middleware\Cors::class,
        ]);

        // Apply localization and JSON encoding after CORS
        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure API routes always return JSON responses
        $exceptions->shouldRenderJsonWhen(function ($request, $exception) {
            return $request->is('api/*');
        });
    })->create();
