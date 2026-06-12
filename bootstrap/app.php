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
            \App\Http\Middleware\EnsureRequestId::class,
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
                $response = new StandardErrorResponse(
                    error: class_basename($e),
                    code: 'UNAUTHENTICATED',
                    message: 'Unauthenticated',
                );
                return response()->json($response->toArray(), 401);
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
                $response = new StandardErrorResponse(
                    error: class_basename($e),
                    code: 'RESOURCE_NOT_FOUND',
                    message: 'Resource not found',
                );
                return response()->json($response->toArray(), 404);
            }
        });
        // ____Database Exceptions____
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                // SQLSTATE 23000: Integrity constraint violation (Unique, Foreign Key)
                if ($e->getCode() === '23000' || $e->getCode() === 23000) {
                    $response = new StandardErrorResponse(
                        error: 'ConflictException',
                        code: 'STATE_CONFLICT',
                        message: 'This record already exists or conflicts with another record.',
                    );
                    return response()->json($response->toArray(), 409);
                }

                Log::error('Database query error', [
                    'message' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'request_id' => $request->header('X-Request-Id'),
                ]);

                $response = new StandardErrorResponse(
                    error: 'DatabaseException',
                    code: 'DATABASE_ERROR',
                    message: 'A database error occurred.',
                );
                return response()->json($response->toArray(), 500);
            }
        });
        
        $exceptions->render(function (\PDOException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error('PDO error', [
                    'message' => $e->getMessage(),
                    'request_id' => $request->header('X-Request-Id'),
                ]);

                $response = new StandardErrorResponse(
                    error: 'DatabaseException',
                    code: 'DATABASE_ERROR',
                    message: 'A database error occurred.',
                );
                return response()->json($response->toArray(), 500);
            }
        });
        // ── Route Not Found ──────────────────────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $response = new StandardErrorResponse(
                    error: class_basename($e),
                    code: 'ENDPOINT_NOT_FOUND',
                    message: 'Endpoint not found.',
                );
                return response()->json($response->toArray(), 404);
            }
        });
        // ── Forbidden ────────────────────────────────────────────────────
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning('Authorization failure / Privilege escalation attempt', [
                    'user_id' => $request->user('api')?->id ?? 'guest',
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                    'request_id' => $request->header('X-Request-Id'),
                ]);

                $response = new StandardErrorResponse(
                    error: class_basename($e),
                    code: 'FORBIDDEN',
                    message: $e->getMessage() ?: 'Forbidden.',
                );
                return response()->json($response->toArray(), 403);
            }
        });
        // ── Rate Limiting ────────────────────────────────────────────────
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $response = new StandardErrorResponse(
                    error: class_basename($e),
                    code: 'TOO_MANY_REQUESTS',
                    message: 'Too many requests. Please try again later.',
                );
                return response()->json($response->toArray(), 429);
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
                        'request_id' => $request->header('X-Request-Id'),
                        'user_id' => $request->user('api')?->id ?? 'guest',
                        'path' => $request->path(),
                        'method' => $request->method(),
                    ]);
                }

                $message = $status === 500
                    ? 'An unexpected error occurred.'
                    : ($e->getMessage() ?: 'Error');

                $details = [];
                if (app()->hasDebugModeEnabled()) {
                    $details = [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                }

                $response = new StandardErrorResponse(
                    error: class_basename($e),
                    code: $status === 500 ? 'INTERNAL_ERROR' : 'ERROR',
                    message: $message,
                    details: $details,
                );

                return response()->json($response->toArray(), $status);
            }
        });
    })->create();
