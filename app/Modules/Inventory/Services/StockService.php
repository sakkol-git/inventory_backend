<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Enums\StockStatus;
use App\Modules\Core\Models\User;
use App\Modules\Core\Services\Crud\TransactionService;
use App\Modules\Inventory\Enums\TransactionAction;
use App\Modules\Inventory\Models\PlantStock;
use Illuminate\Support\Facades\DB;

/**
 * Guards all PlantStock inventory mutations — quantity can never go below zero.
 */
class StockService
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Consume (decrease) stock by the given amount.
     *
     * @throws InsufficientStockException
     */
    public function consume(PlantStock $stock, int $quantity, User $user): PlantStock
    {
        return DB::transaction(function () use ($stock, $quantity, $user): PlantStock {
            $stock = PlantStock::lockForUpdate()->findOrFail($stock->id);
            
            if ($stock->available_quantity < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: $stock->available_quantity,
                );
            }

            $stock->update(['quantity' => $stock->quantity - $quantity]);
            $this->syncStatus($stock);

            $this->transactionService->log($stock, $user, TransactionAction::CONSUMED, $quantity);

            return $stock->refresh();
        });
    }

    /**
     * Reserve stock (increase reserved_quantity).
     *
     * @throws InsufficientStockException
     */
    public function reserve(PlantStock $stock, int $quantity, User $user): PlantStock
    {
        return DB::transaction(function () use ($stock, $quantity, $user): PlantStock {
            $stock = PlantStock::lockForUpdate()->findOrFail($stock->id);
            
            if ($stock->available_quantity < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: $stock->available_quantity,
                );
            }

            $stock->update(['reserved_quantity' => $stock->reserved_quantity + $quantity]);
            $this->syncStatus($stock);

            $this->transactionService->log($stock, $user, TransactionAction::RESERVED, $quantity);

            return $stock->refresh();
        });
    }

    /**
     * Release previously reserved stock.
     */
    public function release(PlantStock $stock, int $quantity, User $user): PlantStock
    {
        return DB::transaction(function () use ($stock, $quantity, $user): PlantStock {
            $stock = PlantStock::lockForUpdate()->findOrFail($stock->id);
            $releaseQty = min($quantity, $stock->reserved_quantity);
            
            $stock->update(['reserved_quantity' => $stock->reserved_quantity - $releaseQty]);
            $this->syncStatus($stock);

            $this->transactionService->log($stock, $user, TransactionAction::RELEASED, $releaseQty);

            return $stock->refresh();
        });
    }

    /**
     * Restore stock after a return.
     */
    public function restock(PlantStock $stock, int $quantity, User $user): PlantStock
    {
        return DB::transaction(function () use ($stock, $quantity, $user): PlantStock {
            $stock = PlantStock::lockForUpdate()->findOrFail($stock->id);
            $stock->update(['quantity' => $stock->quantity + $quantity]);
            $this->syncStatus($stock);

            $this->transactionService->log($stock, $user, TransactionAction::RESTOCKED, $quantity);

            return $stock->refresh();
        });
    }

    /**
     * Automatically set status based on current inventory levels.
     */
    public function syncStatus(PlantStock $stock): void
    {
        $stock->refresh();

        if ($stock->quantity <= 0) {
            $stock->update(['status' => StockStatus::OUT_OF_STOCK]);
        } elseif ($stock->reserved_quantity >= $stock->quantity) {
            $stock->update(['status' => StockStatus::RESERVED]);
        } else {
            $stock->update(['status' => StockStatus::AVAILABLE]);
        }
    }
}
