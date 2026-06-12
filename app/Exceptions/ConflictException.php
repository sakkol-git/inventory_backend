<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a domain operation causes a state conflict.
 */
class ConflictException extends DomainException
{
    protected int $statusCode = 409;
    protected string $errorCode = 'STATE_CONFLICT';

    public function __construct(string $message = 'The operation conflicts with the current state.', array $context = [])
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
