<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Enums\StockStatus;
use App\Modules\Inventory\Models\PlantStock;
use Illuminate\Support\Facades\DB;

/**
 * Guards all PlantStock inventory mutations — quantity can never go below zero.
 */
class StockService
{
    /**
     * Consume (decrease) stock by the given amount.
     *
     * @throws InsufficientStockException
     */
    public function consume(PlantStock $stock, int $quantity): PlantStock
    {
        if ($stock->available_quantity < $quantity) {
            throw new InsufficientStockException(
                requested: $quantity,
                available: $stock->available_quantity,
            );
        }

        return DB::transaction(function () use ($stock, $quantity): PlantStock {
            $stock->decrement('quantity', $quantity);
            $this->syncStatus($stock);

            return $stock->refresh();
        });
    }

    /**
     * Reserve stock (increase reserved_quantity).
     *
     * @throws InsufficientStockException
     */
    public function reserve(PlantStock $stock, int $quantity): PlantStock
    {
        if ($stock->available_quantity < $quantity) {
            throw new InsufficientStockException(
                requested: $quantity,
                available: $stock->available_quantity,
            );
        }

        return DB::transaction(function () use ($stock, $quantity): PlantStock {
            $stock->increment('reserved_quantity', $quantity);
            $this->syncStatus($stock);

            return $stock->refresh();
        });
    }

    /**
     * Release previously reserved stock.
     */
    public function release(PlantStock $stock, int $quantity): PlantStock
    {
        $releaseQty = min($quantity, $stock->reserved_quantity);

        return DB::transaction(function () use ($stock, $releaseQty): PlantStock {
            $stock->decrement('reserved_quantity', $releaseQty);
            $this->syncStatus($stock);

            return $stock->refresh();
        });
    }

    /**
     * Restore stock after a return.
     */
    public function restock(PlantStock $stock, int $quantity): PlantStock
    {
        return DB::transaction(function () use ($stock, $quantity): PlantStock {
            $stock->increment('quantity', $quantity);
            $this->syncStatus($stock);

            return $stock->refresh();
        });
    }

    /**
     * Automatically set status based on current inventory levels.
     */
    private function syncStatus(PlantStock $stock): void
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
