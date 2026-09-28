<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | Stripe Webhook
        |--------------------------------------------------------------------------
        |
        | Stripe cannot send a Laravel CSRF token.
        | Therefore, exclude the webhook endpoint.
        |
        */

        $middleware->validateCsrfTokens(
            except: [
                'stripe/webhook',
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();