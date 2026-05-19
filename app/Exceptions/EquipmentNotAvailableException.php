<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when an equipment item is not available for borrowing due to its condition or status.
 */
class EquipmentNotAvailableException extends DomainException
{
    protected int $statusCode = 422;

    protected string $errorCode = 'EQUIPMENT_NOT_AVAILABLE';

    public function __construct(
        public readonly int $equipmentId,
        public readonly string $reason,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "Equipment (ID: {$equipmentId}) is not available: {$reason}";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'equipment_id' => $this->equipmentId,
            'reason' => $this->reason,
        ];
    }
}
