<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;

class BorrowRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BorrowRecord $record): bool
    {
        return $user->id === $record->user_id
            || $user->hasAnyRole(['admin', 'manager']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, BorrowRecord $record): bool
    {
        return $user->hasAnyRole(['admin', 'manager']) && $record->status === BorrowStatus::PENDING;
    }

    public function reject(User $user, BorrowRecord $record): bool
    {
        return $user->hasAnyRole(['admin', 'manager']) && $record->status === BorrowStatus::PENDING;
    }

    public function returnItem(User $user, BorrowRecord $record): bool
    {
        $active = [BorrowStatus::APPROVED, BorrowStatus::BORROWED, BorrowStatus::OVERDUE];

        return (in_array($record->status, $active, true) && $user->id === $record->user_id)
            || $user->hasAnyRole(['admin', 'manager']);
    }
}
