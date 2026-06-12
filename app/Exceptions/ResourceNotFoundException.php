<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a specific domain resource is not found (alternative to ModelNotFoundException).
 */
class ResourceNotFoundException extends DomainException
{
    protected int $statusCode = 404;
    protected string $errorCode = 'RESOURCE_NOT_FOUND';

    public function __construct(string $message = 'The requested resource was not found.', array $context = [])
    {
        $this->context = $context;
        parent::__construct($message);
    }

    private array $context = [];

    public function getContext(): array
    {
        return $this->context;
    }
}
