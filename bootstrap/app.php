<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
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
                        'version' => '1.0.0',
                    ],
                ], 401);
            }

            return null;
        });

        // Novo: Tratamento para AuthorizationException
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => $e->getMessage() ?: 'Esta ação não é autorizada.',
                    'data'    => [],
                    'error'   => [
                        'code' => 'UNAUTHORIZED_ACCESS',
                        'type' => 'AuthorizationException',
                        'timestamp' => now()->toISOString(),
                        'suggestions' => [
                            'Verifique se você possui as permissões necessárias',
                            'Entre em contato com o administrador se necessário'
                        ]
                    ],
                    'meta'    => [
                        'version' => '1.0.0',
                    ],
                ], 403);
            }

            return null; // Comportamento padrão para requisições web
        });
    })->create();
