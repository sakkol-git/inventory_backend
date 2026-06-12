<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Notification\BorrowRecord\RequestBorrowNotification;
use Illuminate\Support\Facades\DB;

class RequestBorrowService
{
    public function __construct(
        private readonly BorrowableResolver $borrowableResolver,
    ) {}

    /**
     * Create a borrow request for equipment, chemical, or plant sample.
     *
     * @param  array{borrowable_id: int, quantity?: int, due_at?: string, notes?: string}  $data
     */
    public function requestBorrow(User $user, array $data): BorrowRecord
    {
        $record = DB::transaction(function () use ($user, $data) {
            $borrowableType = (string) ($data['borrowable_type'] ?? 'equipment');
            $quantity = (int) ($data['quantity'] ?? 1);
            $borrowable = $this->borrowableResolver->resolve($borrowableType, (int) $data['borrowable_id'], true);

            $this->borrowableResolver->assertBorrowable($borrowable, $quantity);

            return BorrowRecord::create([
                'borrowable_type' => $borrowable->getMorphClass(),
                'borrowable_id' => $borrowable->getKey(),
                'user_id' => $user->id,
                'status' => BorrowStatus::PENDING,
                'quantity' => $quantity,
                'due_at' => $data['due_at'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        // Send notifications outside transaction to prevent blocking on queue failures
        User::role([UserRole::ADMIN->value, UserRole::LAB_MANAGER->value], 'api')->each(
            function (User $mgr) use ($record) {
                try {
                    $mgr->notify(new RequestBorrowNotification($record));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to dispatch borrow request notification', [
                        'borrow_record_id' => $record->id,
                        'manager_id' => $mgr->id,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        );

        return $record->refresh();
    }

    /**
     * Direct borrow for privileged users (skips pending state).
     *
     * @param  array{borrowable_id: int, quantity?: int, due_at?: string, notes?: string}  $data
     */
    public function borrow(User $user, array $data): BorrowRecord
    {
        $borrowableType = (string) ($data['borrowable_type'] ?? 'equipment');
        $quantity = (int) ($data['quantity'] ?? 1);

        return DB::transaction(function () use ($user, $data, $borrowableType, $quantity): BorrowRecord {
            $borrowable = $this->borrowableResolver->resolve($borrowableType, (int) $data['borrowable_id'], true);
            $this->borrowableResolver->assertBorrowable($borrowable, $quantity);

            $record = BorrowRecord::create([
                'borrowable_type' => $borrowable->getMorphClass(),
                'borrowable_id' => $borrowable->getKey(),
                'user_id' => $user->id,
                'status' => BorrowStatus::BORROWED,
                'quantity' => $quantity,
                'borrowed_at' => now(),
                'due_at' => $data['due_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            $this->borrowableResolver->applyBorrow($borrowable, $user, $quantity);

            return $record->refresh();
        });
    }
}
