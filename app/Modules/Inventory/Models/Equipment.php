<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Concerns\EscapesSearchTerm;
use App\Modules\Core\Concerns\HasActivityLogging;
use App\Modules\Core\Concerns\HasImageUpload;
use App\Modules\Core\Concerns\HasTransactions;
use App\Modules\Inventory\Enums\EquipmentCategory;
use App\Modules\Inventory\Enums\EquipmentCondition;
use App\Modules\Inventory\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Equipment extends Model
{
    use EscapesSearchTerm, HasActivityLogging, HasFactory, HasImageUpload, HasTransactions, SoftDeletes;
    protected $table = 'equipment';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::tags(['equipment'])->flush());
        static::deleted(fn () => Cache::tags(['equipment'])->flush());
    }

    protected $fillable = [
        'equipment_name',
        'equipment_code',
        'category',
        'status',
        'condition',
        'location',
        'manufacturer',
        'model_name',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'description',
        'image_url',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'category' => EquipmentCategory::class,
            'status' => EquipmentStatus::class,
            'condition' => EquipmentCondition::class,
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('status', EquipmentStatus::AVAILABLE);
    }

    #[Scope]
    protected function borrowed(Builder $query): void
    {
        $query->where('status', EquipmentStatus::BORROWED);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if (! $term) {
            return;
        }

        $escaped = $this->escapeLike($term);

        $query->where(function (Builder $q) use ($escaped): void {
            $q->where('equipment_name', 'like', "%{$escaped}%")
                ->orWhere('equipment_code', 'like', "%{$escaped}%")
                ->orWhere('manufacturer', 'like', "%{$escaped}%")
                ->orWhere('model_name', 'like', "%{$escaped}%")
                ->orWhere('serial_number', 'like', "%{$escaped}%");
        });
    }

    protected function getIsBorrowableAttribute(): bool
    {
        return $this->status === EquipmentStatus::AVAILABLE
            && $this->condition !== EquipmentCondition::BROKEN;
    }
}
