<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when attempting to process a borrow request that has already been processed.
 */
class BorrowRequestAlreadyProcessedException extends DomainException
{
    protected int $statusCode = 422;

    protected string $errorCode = 'BORROW_REQUEST_ALREADY_PROCESSED';

    public function __construct(
        public readonly int $borrowRecordId,
        public readonly string $currentStatus,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "Borrow request (ID: {$borrowRecordId}) has already been processed with status: {$currentStatus}";
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
        ];
    }
}
