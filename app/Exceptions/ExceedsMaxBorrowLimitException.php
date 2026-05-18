<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a user has exceeded their maximum borrow limit.
 */
class ExceedsMaxBorrowLimitException extends DomainException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'EXCEEDS_MAX_BORROW_LIMIT';

    public function __construct(
        public readonly int $userId,
        public readonly int $currentBorrows,
        public readonly int $maxLimit,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "User has exceeded maximum borrow limit. Current: {$currentBorrows}, Max: {$maxLimit}";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'user_id' => $this->userId,
            'current_borrows' => $this->currentBorrows,
            'max_limit' => $this->maxLimit,
        ];
    }
}
