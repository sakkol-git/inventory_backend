<?php

declare(strict_types=1);

namespace App\Exceptions;

class InsufficientStockException extends DomainException
{
    protected int $statusCode = 422;

    protected string $errorCode = 'INSUFFICIENT_STOCK';

    public function __construct(
        public readonly int $requested,
        public readonly int $available,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "Insufficient stock. Requested: {$requested}, Available: {$available}";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'requested' => $this->requested,
            'available' => $this->available,
        ];
    }
}
