<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Exceptions\InvalidBorrowStatusTransitionException;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Notification\BorrowRecord\ReturnItemNotification;
use Illuminate\Support\Facades\DB;

class ReturnEquipmentService
{
    /**
     * Record the return of borrowed equipment and update statuses.
     *
     * @throws InvalidBorrowStatusTransitionException
     */
    public function __construct(
        public readonly BorrowableResolver $borrowableResolver
    ) {}

    public function returnItem(User $user, BorrowRecord $record): BorrowRecord
    {
        $updatedRecord = DB::transaction(function () use ($user, $record) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);
            $borrowable = $this->borrowableResolver->resolve($record->borrowable_type, $record->borrowable_id, true);

            if (! $record->status->canTransitionTo(BorrowStatus::RETURNED)) {
                throw new InvalidBorrowStatusTransitionException(
                    $record->id,
                    $record->status->value,
                    BorrowStatus::RETURNED->value,
                    'Only approved, borrowed, or overdue records can be returned.'
                );
            }

            $record->update([
                'status' => BorrowStatus::RETURNED,
                'returned_at' => now(),
            ]);

            $this->borrowableResolver->applyReturn($borrowable, $user, $record->quantity);

            return $record->refresh();
        });

        // Send notification outside transaction to prevent blocking on queue failures
        $updatedRecord->user->notify(new ReturnItemNotification($updatedRecord));

        return $updatedRecord;
    }
}
