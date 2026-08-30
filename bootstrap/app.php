<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [HandleInertiaRequests::class]);

        // Ensure API routes don't go through web middleware
        $middleware->api(prepend: []);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => RegistrationErrorCode::messageFor(
                        RegistrationErrorCode::TOO_MANY_ATTEMPTS,
                    ),
                    'code' => RegistrationErrorCode::TOO_MANY_ATTEMPTS,
                    'data' => [],
                ], 429, $e->getHeaders());
            }

            return null;
        });
    })->create();
