<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        apiPrefix: 'v1',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'v1/*',
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Bad Request (400)
        $exceptions->renderable(function (BadRequestHttpException $e) {
            return response()->json([
                'responseMessage' => 'Bad Request: ' . $e->getMessage(),
            ], 400);
        });
        // Unauthorized (JWT)
        $exceptions->renderable(function (UnauthorizedHttpException $e) {
            return response()->json([
                'responseMessage' => 'Unauthorized access, please authenticate.',
            ], 401);
        });

        // Validation Error
        $exceptions->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'responseMessage' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        // No Permission
        $exceptions->renderable(function (UnauthorizedException $e) {
            return response()->json([
                'responseMessage' => 'You do not have the required authorization.',
            ], 403);
        });

        // Not Found (404)
        $exceptions->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'responseMessage' => 'The requested resource was not found.',
            ], 404);
        });

        // General HTTP Error Handler
        $exceptions->renderable(function (HttpException $e) {
            return response()->json([
                'responseMessage' => $e->getMessage() ?: 'An unexpected error occurred.',
            ], $e->getStatusCode() ?: 500);
        });
    })->create();
