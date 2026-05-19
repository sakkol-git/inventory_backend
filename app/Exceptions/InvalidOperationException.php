<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when an invalid operation is attempted (catch-all for other domain logic violations).
 */
class InvalidOperationException extends DomainException
{
    protected int $statusCode = 422;

    protected string $errorCode = 'INVALID_OPERATION';

    public function __construct(
        public readonly string $operation,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "Invalid operation: {$operation}";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'operation' => $this->operation,
        ];
    }
}
