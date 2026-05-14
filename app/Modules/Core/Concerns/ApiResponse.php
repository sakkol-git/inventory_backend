<?php

declare(strict_types=1);

namespace App\Modules\Core\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Standardized API response envelope.
 *
 * All responses follow the format:
 * {
 *     "status": "success"|"error",
 *     "message": "...",
 *     "data": { ... } | [ ... ],
 *     "meta": { ... }  // pagination, timestamps, etc.
 * }
 */
trait ApiResponse
{
    /**
     * Return a success response with data.
     */
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $payload = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'request_id' => request()->header('X-Request-ID') ?? null,
        ];

        if ($meta !== [] && is_array($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * Return a success response wrapping a JsonResource.
     */
    protected function resource(
        JsonResource $resource,
        ?string $message = null,
        int $status = 200,
    ): JsonResponse {
        $data = $resource->resolve();

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return a paginated success response from a LengthAwarePaginator.
     */
    protected function paginated($paginator, ?string $message = null): JsonResponse
    {
        return $this->success(
            $paginator->items(),
            $message,
            200,
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        );
    }

    /**
     * Return a success response with no data (e.g. after delete).
     */
    protected function deleted(string $message = 'Resource deleted successfully.'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => null,
        ]);
    }

    /**
     * Return an error response.
     */
    protected function error(
        string $message,
        array $errors = [],
        ?string $code = null,
        int $status = 400
    ): JsonResponse {
        $payload = [
            'status' => 'error',
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
            'request_id' => request()->header('X-Request-ID') ?? null,
        ];

        return response()->json($payload, $status);
    }
}
