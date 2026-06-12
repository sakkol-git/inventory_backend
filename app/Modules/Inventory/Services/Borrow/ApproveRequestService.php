<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Exceptions\InvalidBorrowStatusTransitionException;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Notification\BorrowRecord\BorrowApprovedNotification;
use Illuminate\Support\Facades\DB;

class ApproveRequestService
{
    /**
     * Approve a borrow request and update equipment status.
     *
     * @throws InvalidBorrowStatusTransitionException
     */
    public function __construct(
        public readonly BorrowableResolver $borrowableResolver
    ) {}

    public function approveBorrow(User $reviewer, BorrowRecord $record): BorrowRecord
    {
        $updatedRecord = DB::transaction(function () use ($reviewer, $record) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);
            $borrowable = $this->borrowableResolver->resolve($record->borrowable_type, $record->borrowable_id, true);

            if ($record->status !== BorrowStatus::PENDING) {
                throw new InvalidBorrowStatusTransitionException(
                    $record->id,
                    $record->status->value,
                    BorrowStatus::BORROWED->value,
                    'Only pending records can be approved.'
                );
            }

            $this->borrowableResolver->assertBorrowable($borrowable, $record->quantity);

            $record->update([
                'status' => BorrowStatus::BORROWED,
                'borrowed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejected_reason' => null,
            ]);

            $this->borrowableResolver->applyBorrow($borrowable, $record->user, $record->quantity);

            return $record->refresh();
        });

        // Send notification outside transaction to prevent blocking on queue failures
        try {
            $updatedRecord->user->notify(new BorrowApprovedNotification($updatedRecord));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to dispatch borrow approval notification', [
                'borrow_record_id' => $updatedRecord->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        return $updatedRecord;
    }
}
