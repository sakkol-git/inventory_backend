<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Notification\BorrowRecord\BorrowRejectNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectRequestService
{
    // This service will handle the rejection logic for borrow requests, including validating the request,
    // updating the borrow record status, and sending notifications to the borrower.

    public function rejectBorrow(User $reviewer, BorrowRecord $record, array $data = []): BorrowRecord
    {
        return DB::transaction(function () use ($reviewer, $record) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);

            if (! $record->status->canTransitionTo(BorrowStatus::REJECTED)) {
                throw ValidationException::withMessages([
                    'status' => 'Only pending records can be rejected.',
                ]);
            }

            $record->update([
                'status' => BorrowStatus::REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejected_reason' => $data['rejected_reason'] ?? null,
            ]);

            $record->user->notify(new BorrowRejectNotification($record));

            return $record->refresh();
        });
    }
}
