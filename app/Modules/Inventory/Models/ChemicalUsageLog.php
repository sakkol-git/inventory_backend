<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Models\User;
use Database\Factories\ChemicalUsageLogFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChemicalUsageLog extends Model
{
    /** @use HasFactory<ChemicalUsageLogFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'chemical_usage_logs';

    protected $fillable = [
        'chemical_id',
        'user_id',
        'quantity_used',
        'unit',
        'purpose',
        'experiment_name',
        'used_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used' => 'decimal:2',
            'used_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function chemical(): BelongsTo
    {
        return $this->belongsTo(Chemical::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    #[Scope]
    protected function forChemical(Builder $query, int $chemicalId): void
    {
        $query->where('chemical_id', $chemicalId);
    }

    #[Scope]
    protected function forUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    #[Scope]
    protected function recent(Builder $query, int $days = 30): void
    {
        $query->where('used_at', '>=', now()->subDays($days));
    }

    #[Scope]
    protected function betweenDates(Builder $query, string $from, string $to): void
    {
        $query->whereBetween('used_at', [$from, $to]);
    }
}
