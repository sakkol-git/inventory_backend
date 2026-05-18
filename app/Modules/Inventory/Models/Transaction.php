<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\TransactionAction;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'transactionable_type',
        'transactionable_id',
        'action',
        'quantity',
        'balance_after',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'action' => TransactionAction::class,
            'quantity' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    /**
     * The item this transaction is about (PlantStock, Chemical, Equipment, etc.).
     * This is the core of the polymorphic design — one Transaction table
     * serves ALL inventory types, avoiding N separate log tables.
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /** The user who performed the action. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    #[Scope]
    protected function forAction(Builder $query, TransactionAction $action): void
    {
        $query->where('action', $action);
    }

    #[Scope]
    protected function forType(Builder $query, string $type): void
    {
        $query->where('transactionable_type', $type);
    }

    #[Scope]
    protected function recent(Builder $query, int $days = 7): void
    {
        $query->where('created_at', '>=', now()->subDays($days));
    }
}
