<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Exceptions\ChemicalExpiredException;
use App\Exceptions\StockCannotBeNegativeException;
use App\Modules\Core\Models\User;
use App\Modules\Core\Services\Crud\TransactionService;
use App\Modules\Inventory\Enums\TransactionAction;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\ChemicalUsageLog;
use Illuminate\Support\Facades\DB;

class ChemicalUsageService
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Record a chemical usage entry, decrement stock, and log the transaction.
     *
     * @param  array{chemical_id: int, quantity_used: float, notes?: string, ...}  $data
     *
     * @throws StockCannotBeNegativeException
     */
    public function create(array $data, User $user): ChemicalUsageLog
    {
        $data['user_id'] = $user->id;

        return DB::transaction(function () use ($data, $user): ChemicalUsageLog {
            $chemical = Chemical::query()
                ->lockForUpdate()
                ->findOrFail($data['chemical_id']);

            if ($chemical->is_expired) {
                throw new ChemicalExpiredException(
                    $chemical->id,
                    $chemical->expiry_date?->toDateString() ?? 'unknown'
                );
            }

            $quantityUsed = $data['quantity_used'];
            $decrementQty = (int) ceil($quantityUsed);

            if ($decrementQty <= 0) {
                throw new StockCannotBeNegativeException(
                    'Chemical',
                    $chemical->quantity,
                    -$decrementQty,
                    'Quantity used must be greater than zero.'
                );
            }

            if ($chemical->quantity < $decrementQty) {
                throw new StockCannotBeNegativeException(
                    'Chemical',
                    $chemical->quantity,
                    $decrementQty,
                    'Insufficient chemical stock available.'
                );
            }

            $log = ChemicalUsageLog::create($data);

            // Decrement chemical stock
            $chemical->decrement('quantity', $decrementQty);

            $this->transactionService->log(
                item: $chemical,
                user: $user,
                action: TransactionAction::CONSUMED,
                quantity: $quantityUsed,
            );

            return $log;
        });
    }
}
