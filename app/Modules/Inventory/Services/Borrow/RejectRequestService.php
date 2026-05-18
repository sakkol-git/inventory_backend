<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Exceptions\InvalidBorrowStatusTransitionException;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Notification\BorrowRecord\BorrowRejectNotification;
use Illuminate\Support\Facades\DB;

class RejectRequestService
{
    /**
     * Reject a borrow request with optional reason.
     *
     * @param User $reviewer The user rejecting the request
     * @param BorrowRecord $record The borrow request to reject
     * @param array{rejected_reason?: string} $data Optional rejection reason
     *
     * @throws InvalidBorrowStatusTransitionException
     */
    public function rejectBorrow(User $reviewer, BorrowRecord $record, array $data = []): BorrowRecord
    {
        $updatedRecord = DB::transaction(function () use ($reviewer, $record, $data) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);

            if (! $record->status->canTransitionTo(BorrowStatus::REJECTED)) {
                throw new InvalidBorrowStatusTransitionException(
                    $record->id,
                    $record->status->value,
                    BorrowStatus::REJECTED->value,
                    'Only pending records can be rejected.'
                );
            }

            $record->update([
                'status' => BorrowStatus::REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejected_reason' => $data['rejected_reason'] ?? null,
            ]);

            return $record->refresh();
        });

        // Send notification outside transaction to prevent blocking on queue failures
        $updatedRecord->user->notify(new BorrowRejectNotification($updatedRecord));

        return $updatedRecord;
    }
}
