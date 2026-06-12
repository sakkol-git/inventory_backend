<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a filesystem or storage operation fails.
 */
class StorageException extends DomainException
{
    protected int $statusCode = 500;
    protected string $errorCode = 'STORAGE_ERROR';

    public function __construct(string $message = 'A storage operation failed.', array $context = [])
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
