<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when an operation would result in negative stock quantity.
 */
class StockCannotBeNegativeException extends DomainException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'STOCK_CANNOT_BE_NEGATIVE';

    public function __construct(
        public readonly string $model,
        public readonly int $currentQuantity,
        public readonly int $requestedDecrease,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "{$model} stock cannot go negative. Current: {$currentQuantity}, Requested decrease: {$requestedDecrease}";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'model' => $this->model,
            'current_quantity' => $this->currentQuantity,
            'requested_decrease' => $this->requestedDecrease,
        ];
    }
}
