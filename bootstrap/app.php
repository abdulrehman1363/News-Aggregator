<?php

use App\Exceptions\NewsProviderException;
use App\Exceptions\RepositoryException;
use App\Exceptions\ServiceException;
use App\Http\Middleware\CompressResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add CompressResponse middleware to API routes
        $middleware->api(append: [
            CompressResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle custom exceptions
        $exceptions->render(function (RepositoryException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], $e->getCode() ?: 500);
            }
        });

        $exceptions->render(function (ServiceException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], $e->getCode() ?: 500);
            }
        });

        $exceptions->render(function (NewsProviderException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], $e->getCode() ?: 500);
            }
        });

        // Handle ModelNotFoundException
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        // Handle Validation Exceptions
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle Authentication Exceptions
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please log in.',
                ], 401);
            }
        });

        // Handle Authorization Exceptions
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to perform this action.',
                ], 403);
            }
        });

        // Handle general exceptions in production
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson() && app()->environment('production')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again later.',
                ], 500);
            }
        });
    })->create();
