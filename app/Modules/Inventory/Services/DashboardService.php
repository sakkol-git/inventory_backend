<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantSpecies;
use App\Modules\Inventory\Models\PlantStock;
use App\Modules\Inventory\Models\PlantVariety;
use App\Modules\Inventory\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Extracted from DashboardController — caches every aggregate query.
 * Fixes B-03: 15+ COUNT queries per request → cache hits.
 */
class DashboardService
{
    /**
     * Inventory entity counts + active/total borrows.
     *
     * @return array<string, int>
     */
    public function getCounts(): array
    {
        return Cache::remember('dashboard:counts', 60, function (): array {
            return [
                'plant_species' => PlantSpecies::count(),
                'plant_varieties' => PlantVariety::count(),
                'plant_samples' => PlantSample::count(),
                'plant_stocks' => PlantStock::count(),
                'chemicals' => Chemical::count(),
                'equipment' => Equipment::count(),
                'users' => User::count(),
                'active_borrows' => BorrowRecord::whereIn('status', [
                    BorrowStatus::BORROWED->value,
                    BorrowStatus::APPROVED->value,
                    BorrowStatus::PENDING->value,
                ])->count(),
                'total_borrows' => BorrowRecord::count(),
            ];
        });
    }

    /**
     * Expiry, overdue, pending, low-stock alerts.
     *
     * @return array<string, int>
     */
    public function getAlerts(): array
    {
        return Cache::remember('dashboard:alerts', 30, function (): array {
            return [
                'expiring_chemicals' => Chemical::query()->expiringSoon()->count(),
                'expired_chemicals' => Chemical::query()->expired()->count(),
                'overdue_borrows' => BorrowRecord::query()->overdue()->count(),
                'pending_borrows' => BorrowRecord::query()->where('status', BorrowStatus::PENDING->value)->count(),
                'low_stock_chemicals' => Chemical::query()->lowStock()->count(),
            ];
        });
    }

    /**
     * Last 10 transactions with user + item eager-loaded.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentActivity(): array
    {
        return Cache::remember('dashboard:recent_activity', 15, function (): array {
            return Transaction::with(['user', 'transactionable'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn (Transaction $tx): array => [
                    'id' => $tx->id,
                    'user' => $tx->user?->name,
                    'action' => $tx->action->value,
                    'item_type' => $tx->transactionable_type,
                    'item_id' => $tx->transactionable_id,
                    'quantity' => $tx->quantity,
                    'note' => $tx->note,
                    'created_at' => $tx->created_at?->toISOString(),
                ])
                ->all();
        });
    }

    /**
     * Group-by breakdowns for borrows, equipment, chemicals.
     *
     * @return array<string, Collection<string, int>>
     */
    public function getStatusBreakdown(): array
    {
        return Cache::remember('dashboard:status_breakdown', 60, function (): array {
            return [
                'borrows_by_status' => BorrowRecord::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'equipment_by_status' => Equipment::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'chemicals_by_category' => Chemical::selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->pluck('count', 'category'),
            ];
        });
    }
}
