<?php

use App\Exceptions\DomainException;
use App\Exceptions\StandardErrorResponse;
use App\Modules\Core\Http\Middleware\AdminMiddleware;
use App\Modules\Core\Http\Middleware\CacheApiResponse;
use App\Modules\Core\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            app_path('Modules/Core/Routes/api.php'),
            app_path('Modules/Inventory/Routes/api.php'),
        ],
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            ThrottleRequests::class.':60,1',
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'cache.api' => CacheApiResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ____Domain Exceptions____
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning('Domain exception occurred', [
                    'exception' => get_class($e),
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ]);

                $errorResponse = StandardErrorResponse::fromDomainException($e);

                return response()->json($errorResponse->toArray(), $e->getStatusCode());
            }
        });
        // ____Authentication Exceptions____
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });
        // ____Validation Exceptions____
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                $errorResponse = StandardErrorResponse::fromValidationErrors($e->errors());

                return response()->json($errorResponse->toArray(), 422);
            }
        });
        // ____Model Not Found Exceptions____
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }
        });
        // ── Route Not Found ──────────────────────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found.',
                ], 404);
            }
        });
        // ── Forbidden ────────────────────────────────────────────────────
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden.',
                ], 403);
            }
        });
        // ── Rate Limiting ────────────────────────────────────────────────
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            }
        });
        // ── Generic Catch-All (API only) ─────────────────────────────────
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

                // Log all exceptions
                if ($status === 500) {
                    Log::error('Unhandled exception', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }

                $payload = [
                    'success' => false,
                    'message' => $status === 500
                        ? 'An unexpected error occurred.'
                        : ($e->getMessage() ?: 'Error'),
                ];

                if (app()->hasDebugModeEnabled()) {
                    $payload['debug'] = [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                }

                return response()->json($payload, $status);
            }
        });
    })->create();
