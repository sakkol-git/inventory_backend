<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Modules\Core\Models\User;
use App\Modules\Core\Services\Crud\TransactionService;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Enums\EquipmentStatus;
use App\Modules\Inventory\Enums\TransactionAction;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Notification\BorrowRecord\BorrowApprovedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveRequestService
{
    // This service will handle the approval logic for borrow requests, including validating the request,
    // updating the borrow record status, and sending notifications to the borrower.

    public function __construct(
        public readonly TransactionService $transaction
    ) {}

    public function approveBorrow(User $reviewer, BorrowRecord $record): BorrowRecord
    {
        return DB::transaction(function () use ($reviewer, $record) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);
            $equipment = Equipment::lockForUpdate()->findOrFail($record->borrowable_id);

            if (! $record->status->canTransitionTo(BorrowStatus::APPROVED)) {
                throw ValidationException::withMessages([
                    'status' => 'Only pending records can be approved.',
                ]);
            }

            if (! $equipment->is_borrowable) {
                throw ValidationException::withMessages([
                    'borrowable_id' => 'The selected equipment is not available for borrowing.',
                ]);
            }

            $record->update([
                'status' => BorrowStatus::APPROVED,
                'borrowed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejected_reason' => null,
            ]);

            $equipment->update([
                'status' => EquipmentStatus::BORROWED,
            ]);

            $this->transaction->log(
                $equipment,
                $reviewer,
                TransactionAction::BORROWED,
                $record->quantity,
            );

            $record->user->notify(new BorrowApprovedNotification($record));

            return $record->refresh();
        });
    }
}
