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
use App\Modules\Inventory\Notification\BorrowRecord\ReturnItemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnEquipmentService
{
    // This service will handle the logic for returning borrowed equipment, including validating the return

    public function __construct(
        public readonly TransactionService $transaction
    ) {}

    public function returnItem(User $user, BorrowRecord $record): BorrowRecord
    {
        return DB::transaction(function () use ($user, $record) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);
            $equipment = Equipment::lockForUpdate()->findOrFail($record->borrowable_id);

            if (! $record->status->canTransitionTo(BorrowStatus::RETURNED)) {
                throw ValidationException::withMessages([
                    'status' => 'Only approved, borrowed, or overdue records can be returned.',
                ]);
            }

            $record->update([
                'status' => BorrowStatus::RETURNED,
                'returned_at' => now(),
            ]);

            $equipment->update([
                'status' => EquipmentStatus::AVAILABLE,
            ]);

            $this->transaction->log(
                $equipment,
                $user,
                TransactionAction::RETURNED,
                $record->quantity,
            );

            $record->user->notify(new ReturnItemNotification($record));

            return $record->refresh();
        });
    }
}
