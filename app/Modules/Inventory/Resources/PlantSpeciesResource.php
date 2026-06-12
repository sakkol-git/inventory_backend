<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Services\ImageUpload\ImageUploadService;
use App\Modules\Inventory\Models\PlantSpecies;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlantSpecies
 */
class PlantSpeciesResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var PlantSpecies $species */
        $species = $this->resource;

        return [
            'id' => $species->id,
            'common_name' => $species->common_name,
            'khmer_name' => $species->khmer_name,
            'scientific_name' => $species->scientific_name,
            'family' => $species->family,
            // Return the enum value (string) if set
            'growth_type' => $species->growth_type?->value,
            'native_region' => $species->native_region,
            'propagation_method' => $species->propagation_method,
            'description' => $species->description,
            'image_url' => ImageUploadService::resolveImageUrl($species->image_path, $species->image_url),
            'created_at' => $species->created_at?->toISOString(),
            'updated_at' => $species->updated_at?->toISOString(),
        ];
    }
}
