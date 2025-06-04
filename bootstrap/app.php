<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tratamento para AuthenticationException
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Unauthorized access. Invalid or expired token.',
                    'data'    => [],
                    'meta'    => [
                        'version' => config('app.version', '1.0.0'),
                        'timestamp' => now()->toISOString(),
                    ],
                ], 401);
            }

            return null;
        });

        // Tratamento para AuthorizationException (captura antes da conversão)
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => $e->getMessage() ?: 'This action is not authorized.',
                    'data'    => [],
                    'error'   => [
                        'code' => 'UNAUTHORIZED_ACCESS',
                        'suggestions' => [
                            'Check if you have the required permissions',
                        ]
                    ],
                    'meta'    => [
                        'version' => config('app.version', '1.0.0'),
                        'timestamp' => now()->toISOString(),
                    ],
                ], 403);
            }

            return null;
        });

        // Tratamento para AccessDeniedHttpException (fallback)
        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'This action is not authorized.',
                    'data'    => [],
                    'error'   => [
                        'code' => 'ACCESS_DENIED',
                        'suggestions' => [
                            'Check if you have the necessary permissions'
                        ]
                    ],
                    'meta'    => [
                        'version' => config('app.version', '1.0.0'),
                        'timestamp' => now()->toISOString(),
                    ],
                ], 403);
            }

            return null;
        });
    })->create();
