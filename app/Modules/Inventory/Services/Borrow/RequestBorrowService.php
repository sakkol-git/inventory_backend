<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Notification\BorrowRecord\RequestBorrowNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestBorrowService
{
    // This service will handle the logic for requesting to borrow equipment, chemicals, and plant samples.

    public function requestBorrow(User $user, array $data): BorrowRecord
    {
        return DB::transaction(function () use ($user, $data) {
            $equipment = Equipment::lockForUpdate()->findOrFail($data['borrowable_id']);

            if (! $equipment->is_borrowable) {
                throw ValidationException::withMessages([
                    'borrowable_id' => 'The selected equipment is not available for borrowing.',
                ]);
            }

            $record = BorrowRecord::create([
                'borrowable_type' => 'equipment',
                'borrowable_id' => $equipment->id,
                'user_id' => $user->id,
                'status' => BorrowStatus::PENDING,
                'quantity' => $data['quantity'] ?? 1,
                'due_at' => $data['due_at'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Notify managers/admins of new borrow request here (implementation pending)
            User::role(['admin', 'manager'])->each(
                fn ($mgr) => $mgr->notify(new RequestBorrowNotification($record))
            );

            return $record->refresh();
        });
    }
}
