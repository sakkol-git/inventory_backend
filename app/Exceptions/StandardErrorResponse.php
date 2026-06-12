<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Standardized error response data transfer object.
 * Used by the global exception handler to create consistent API error responses.
 */
readonly class StandardErrorResponse
{
    /**
     * @param  array<string, mixed>  $details  Additional context details about the error
     */
    public function __construct(
        public string $error,
        public string $code,
        public string $message,
        public array $details = [],
        public string $timestamp = '',
        public bool $success = false,
        public ?string $correlation_id = null,
    ) {}

    /**
     * Get timestamp, generating it if empty.
     */
    public function getTimestamp(): string
    {
        return $this->timestamp !== '' ? $this->timestamp : now()->toIso8601String();
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlation_id ?? request()->header('X-Request-Id');
    }

    /**
     * Create from a DomainException.
     */
    public static function fromDomainException(DomainException $exception): self
    {
        return new self(
            error: class_basename($exception),
            code: $exception->getErrorCode(),
            message: $exception->getMessage(),
            details: $exception->getContext(),
            timestamp: now()->toIso8601String(),
        );
    }

    /**
     * Create from a generic exception.
     */
    public static function fromException(Throwable $exception): self
    {
        return new self(
            error: class_basename($exception),
            code: 'INTERNAL_ERROR',
            message: $exception->getMessage() ?: 'An unexpected error occurred',
            details: ['file' => $exception->getFile(), 'line' => $exception->getLine()],
            timestamp: now()->toIso8601String(),
        );
    }

    /**
     * Create from validation errors.
     *
     * @param  array<string, array<int, string>>  $errors  Validation errors keyed by field
     */
    public static function fromValidationErrors(array $errors): self
    {
        return new self(
            error: 'ValidationException',
            code: 'VALIDATION_ERROR',
            message: 'Validation failed',
            details: ['errors' => $errors],
            timestamp: now()->toIso8601String(),
        );
    }

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'code' => $this->code,
            'message' => $this->message,
            'details' => $this->details,
            'correlation_id' => $this->getCorrelationId(),
            'timestamp' => $this->getTimestamp(),
        ];
    }
}
