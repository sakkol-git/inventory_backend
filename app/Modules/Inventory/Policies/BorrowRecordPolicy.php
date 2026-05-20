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
        return $this->canManage($user) && $user->hasPermissionTo('borrows.view', 'api');
    }

    public function view(User $user, BorrowRecord $record): bool
    {
        return $user->id === $record->user_id 
            && $user->hasPermissionTo('borrows.view', 'api')
            || $this->canManage($user);
;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('borrows.create', 'api');
    }

    public function approve(User $user, BorrowRecord $record): bool
    {
        return ($this->canManage($user) 
                && $record->status === BorrowStatus::PENDING)
                || ($user->hasPermissionTo('borrows.approve', 'api')
                && $record->status === BorrowStatus::PENDING);
    }

    public function reject(User $user, BorrowRecord $record): bool
    {
        return ($this->canManage($user) 
                && $record->status === BorrowStatus::PENDING)
                || ($user->hasPermissionTo('borrows.reject', 'api')
                && $record->status === BorrowStatus::PENDING);
    }

    public function returnItem(User $user, BorrowRecord $record): bool
    {
        $active = [BorrowStatus::APPROVED, BorrowStatus::BORROWED, BorrowStatus::OVERDUE];

        if (in_array($record->status, $active, true) && $user->id === $record->user_id && $user->hasPermissionTo('borrow.return', 'api')) {
            return true;
        }

        return $this->canManage($user);
    }

    private function canManage(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'lab_manager'], 'api')
            || $user->hasPermissionTo('borrows.approve', 'api');
    }
}
