<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum BorrowStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case BORROWED = 'borrowed';
    case RETURNED = 'returned';
    case OVERDUE = 'overdue';

    public function canTransitionTo(BorrowStatus $next): bool
    {
        return match ($this) {
            self::PENDING => in_array($next, [self::APPROVED, self::BORROWED, self::REJECTED], true),
            self::APPROVED => in_array($next, [self::BORROWED, self::RETURNED, self::OVERDUE]),
            self::BORROWED => in_array($next, [self::RETURNED, self::OVERDUE]),
            self::OVERDUE => $next === self::RETURNED,
            default => false,
        };
    }
}
