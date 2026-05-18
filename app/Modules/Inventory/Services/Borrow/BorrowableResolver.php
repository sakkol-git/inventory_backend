<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Exceptions\ChemicalExpiredException;
use App\Exceptions\EquipmentNotAvailableException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOperationException;
use App\Modules\Core\Models\User;
use App\Modules\Core\Services\Crud\TransactionService;
use App\Modules\Inventory\Enums\EquipmentStatus;
use App\Modules\Inventory\Enums\TransactionAction;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantStock;
use App\Modules\Inventory\Services\StockService;
use Illuminate\Database\Eloquent\Model;

class BorrowableResolver
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly StockService $stockService,
    ) {}

    /**
     * Resolve a borrowable model by type and ID.
     */
    public function resolve(string $borrowableType, int $id, bool $lock = false): Model
    {
        $modelClass = $this->normalizeType($borrowableType);
        $query = $modelClass::query();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }

    /**
     * Validate that the item can be borrowed for the given quantity.
     */
    public function assertBorrowable(Model $borrowable, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidOperationException('borrow_quantity');
        }

        if ($borrowable instanceof Equipment) {
            if ($quantity !== 1) {
                throw new InvalidOperationException('equipment_borrow_quantity');
            }

            if (! $borrowable->is_borrowable) {
                throw new EquipmentNotAvailableException(
                    $borrowable->id,
                    'Equipment is not available for borrowing'
                );
            }

            return;
        }

        if ($borrowable instanceof Chemical) {
            if ($borrowable->is_expired) {
                throw new ChemicalExpiredException(
                    $borrowable->id,
                    $borrowable->expiry_date?->toDateString() ?? 'unknown'
                );
            }

            if ($borrowable->quantity < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: (int) $borrowable->quantity,
                );
            }

            return;
        }

        if ($borrowable instanceof PlantStock) {
            if ($borrowable->available_quantity < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: $borrowable->available_quantity,
                );
            }

            return;
        }

        if ($borrowable instanceof PlantSample) {
            if ($borrowable->stock_quantity !== null && $borrowable->stock_quantity < $quantity) {
                throw new InsufficientStockException(
                    requested: $quantity,
                    available: (int) $borrowable->stock_quantity,
                );
            }

            return;
        }

        throw new InvalidOperationException('unsupported_borrowable');
    }

    /**
     * Apply inventory changes and log the borrow action.
     */
    public function applyBorrow(Model $borrowable, User $actor, int $quantity): void
    {
        if ($borrowable instanceof Equipment) {
            $borrowable->update(['status' => EquipmentStatus::BORROWED]);
            $this->log($borrowable, $actor, TransactionAction::BORROWED, $quantity);

            return;
        }

        if ($borrowable instanceof Chemical) {
            $borrowable->decrement('quantity', $quantity);
            $this->log($borrowable, $actor, TransactionAction::BORROWED, $quantity);

            return;
        }

        if ($borrowable instanceof PlantStock) {
            $this->stockService->consume($borrowable, $quantity);
            $this->log($borrowable, $actor, TransactionAction::BORROWED, $quantity);

            return;
        }

        if ($borrowable instanceof PlantSample) {
            // Decrement from related PlantStock records
            $stock = $borrowable->stocks()
                ->whereNull('deleted_at')
                ->first();

            if ($stock) {
                $this->stockService->consume($stock, $quantity);
            }

            $this->log($borrowable, $actor, TransactionAction::BORROWED, $quantity);

            return;
        }

        throw new InvalidOperationException('unsupported_borrowable');
    }

    /**
     * Apply inventory changes and log the return action.
     */
    public function applyReturn(Model $borrowable, User $actor, int $quantity): void
    {
        if ($borrowable instanceof Equipment) {
            $borrowable->update(['status' => EquipmentStatus::AVAILABLE]);
            $this->log($borrowable, $actor, TransactionAction::RETURNED, $quantity);

            return;
        }

        if ($borrowable instanceof Chemical) {
            $borrowable->increment('quantity', $quantity);
            $this->log($borrowable, $actor, TransactionAction::RETURNED, $quantity);

            return;
        }

        if ($borrowable instanceof PlantStock) {
            $this->stockService->restock($borrowable, $quantity);
            $this->log($borrowable, $actor, TransactionAction::RETURNED, $quantity);

            return;
        }

        if ($borrowable instanceof PlantSample) {
            // Restock to related PlantStock records
            $stock = $borrowable->stocks()
                ->whereNull('deleted_at')
                ->first();

            if ($stock) {
                $this->stockService->restock($stock, $quantity);
            }

            $this->log($borrowable, $actor, TransactionAction::RETURNED, $quantity);

            return;
        }

        throw new InvalidOperationException('unsupported_borrowable');
    }

    /**
     * Normalize a borrowable type to its class.
     */
    private function normalizeType(string $type): string
    {
        $map = [
            'equipment' => Equipment::class,
            Equipment::class => Equipment::class,
            'chemical' => Chemical::class,
            Chemical::class => Chemical::class,
            'plant_stock' => PlantStock::class,
            PlantStock::class => PlantStock::class,
            'plant_sample' => PlantSample::class,
            PlantSample::class => PlantSample::class,
        ];

        if (! isset($map[$type])) {
            throw new InvalidOperationException('unsupported_borrowable');
        }

        return $map[$type];
    }

    private function log(Model $item, User $actor, TransactionAction $action, int $quantity): void
    {
        $this->transactionService->log(
            item: $item,
            user: $actor,
            action: $action,
            quantity: $quantity,
        );
    }
}
