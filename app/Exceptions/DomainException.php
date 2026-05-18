<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for all domain-specific business logic errors.
 * These exceptions represent violations of business rules and constraints.
 */
abstract class DomainException extends Exception
{
    /**
     * HTTP status code for this exception.
     * Default is 422 Unprocessable Entity for business logic violations.
     */
    protected int $statusCode = 422;

    /**
     * Error code for API responses (e.g., "INSUFFICIENT_STOCK").
     */
    protected string $errorCode = 'DOMAIN_ERROR';

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error code for API responses.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get additional context data for error responses.
     * Override in subclasses to provide additional details.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [];
    }
}
