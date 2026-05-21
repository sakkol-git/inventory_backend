<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Concerns\EscapesSearchTerm;
use App\Modules\Core\Concerns\HasActivityLogging;
use App\Modules\Core\Concerns\HasImageUpload;
use App\Modules\Core\Concerns\HasTransactions;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\LabLocation;
use App\Modules\Inventory\Enums\SampleStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantSample extends Model
{
    // traits
    use EscapesSearchTerm, HasActivityLogging, HasAttributes, HasFactory, HasImageUpload, HasTransactions, SoftDeletes;

    // table name
    protected $table = 'plant_samples';

    // fillable fields
    protected $fillable = [
        'plant_variety_id',
        'user_id',
        'sample_name',
        'sample_code',
        'department',
        'origin_location',
        'brought_at',
        'lab_location',
        'status',
        'description',
        'image_url',
        'image_path',
    ];

    // casts
    protected function casts(): array
    {
        return [
            'brought_at' => 'date',
            'lab_location' => LabLocation::class,
            'status' => SampleStatus::class,
            'quantity' => 'integer',
        ];
    }

    // ─── Computed Attributes ─────────────────────────────────────────────────

    /**
     * Calculate total quantity from related PlantStock records.
     * Falls back to the quantity field if no stocks exist.
     */
    protected function getStockQuantityAttribute(): ?int
    {
        $stocksSum = $this->stocks()
            ->whereNull('deleted_at')
            ->sum('quantity');

        return $stocksSum > 0 ? $stocksSum : $this->quantity;
    }

    public function plantVariety(): BelongsTo
    {
        return $this->belongsTo(PlantVariety::class, 'plant_variety_id');
    }

    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(PlantStock::class, 'plant_sample_id');
    }

    // Scopes Search
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if (! $term) {
            return;
        }

        $escaped = $this->escapeLike($term);

        $query->where(function (Builder $q) use ($escaped): void {
            $q->where('sample_name', 'like', "%{$escaped}%")
                ->orWhere('sample_code', 'like', "%{$escaped}%");
        });
    }
}
