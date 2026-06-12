<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when an external HTTP or integration service fails.
 */
class ExternalServiceException extends DomainException
{
    protected int $statusCode = 502; // Bad Gateway
    protected string $errorCode = 'EXTERNAL_SERVICE_FAILURE';

    public function __construct(string $message = 'An external service failed to respond correctly.', array $context = [])
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
