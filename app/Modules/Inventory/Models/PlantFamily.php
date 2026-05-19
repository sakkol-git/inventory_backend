<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Concerns\EscapesSearchTerm;
use App\Modules\Core\Concerns\HasActivityLogging;
use App\Modules\Core\Concerns\HasImageUpload;
use App\Modules\Core\Concerns\HasTransactions;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantFamily extends Model
{
    /** @use HasFactory<PlantFamilyFactory> */
    use EscapesSearchTerm, HasActivityLogging, HasFactory, HasImageUpload, HasTransactions, SoftDeletes;

    protected $table = 'plant_families';

    protected $fillable = [
        'name',
        'image_url',
        'image_path',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function species(): HasMany
    {
        return $this->hasMany(PlantSpecies::class, 'plant_family_id');
    }

    // public function experiments(): HasMany
    // {
    //     return $this->hasMany(Experiment::class, 'plant_species_id');
    // }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    #[Scope]
    protected function family(Builder $query, string $family): void
    {
        $query->where('name', $family);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if (! $term) {
            return;
        }

        $escaped = $this->escapeLike($term);

        $query->where(function (Builder $q) use ($escaped): void {
            $q->where('name', 'like', "%{$escaped}%");
        });
    }
}
