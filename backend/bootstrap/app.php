<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'v1/*',
        ]);

        $middleware->alias([
            'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (UnauthorizedHttpException $e) {
            return response()->json([
                'responseMessage' => 'Unauthorized access, please authenticate.',
            ], 401);
        });

        $exceptions->renderable(function (UnauthorizedException $e) {
            return response()->json([
                'responseMessage' => 'You do not have the required authorization.',
            ], 403);
        });

        $exceptions->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'responseMessage' => 'The requested resource was not found.',
            ], 404);
        });

        $exceptions->renderable(function (HttpException $e) {
            return response()->json([
                'responseMessage' => 'An unexpected error occurred on the server.',
            ], 500);
        });
    })->create();
