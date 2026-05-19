<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a chemical has expired and cannot be used or borrowed.
 */
class ChemicalExpiredException extends DomainException
{
    protected int $statusCode = 422;

    protected string $errorCode = 'CHEMICAL_EXPIRED';

    public function __construct(
        public readonly int $chemicalId,
        public readonly string $expiryDate,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = "Chemical (ID: {$chemicalId}) expired on {$expiryDate} and cannot be used";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'chemical_id' => $this->chemicalId,
            'expiry_date' => $this->expiryDate,
        ];
    }
}
