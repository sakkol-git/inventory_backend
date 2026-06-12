<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a business rule constraint is violated.
 */
class BusinessRuleException extends DomainException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'BUSINESS_RULE_VIOLATION';

    public function __construct(string $message = 'A business rule was violated.', array $context = [])
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
