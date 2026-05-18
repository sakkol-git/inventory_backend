<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a user attempts to perform an unauthorized action.
 * Use for business logic authorization, not authentication.
 */
class UnauthorizedAccessException extends DomainException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'UNAUTHORIZED_ACCESS';

    public function __construct(
        public readonly string $action,
        public readonly ?string $resource = null,
        string $message = '',
    ) {
        if ($message === '' || $message === '0') {
            $message = $this->resource
                ? "Unauthorized to perform action '{$action}' on {$this->resource}"
                : "Unauthorized to perform action '{$action}'";
        }

        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'action' => $this->action,
            'resource' => $this->resource,
        ];
    }
}
