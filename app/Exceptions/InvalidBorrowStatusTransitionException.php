<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when attempting an invalid status transition for a borrow record.
 */
class InvalidBorrowStatusTransitionException extends DomainException
{
    protected int $statusCode = 422;

    protected string $errorCode = 'INVALID_BORROW_STATUS_TRANSITION';

    public function __construct(
        public readonly int $borrowRecordId,
        public readonly string $currentStatus,
        public readonly string $attemptedStatus,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "Cannot transition borrow request from '{$currentStatus}' to '{$attemptedStatus}'";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'borrow_record_id' => $this->borrowRecordId,
            'current_status' => $this->currentStatus,
            'attempted_status' => $this->attemptedStatus,
        ];
    }
}
