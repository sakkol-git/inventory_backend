<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Services\ImageUpload\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Inventory\Models\Chemical
 */
class ChemicalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Modules\Inventory\Models\Chemical $chemical */
        $chemical = $this->resource;

        /** @return array<string, mixed> */
        return [
            'id' => $chemical->id,
            'common_name' => $chemical->common_name,
            'chemical_code' => $chemical->chemical_code,
            'category' => $chemical->category?->value,
            'quantity' => $chemical->quantity,
            'storage_location' => $chemical->storage_location,
            'expiry_date' => $chemical->expiry_date?->format('Y-m-d'),
            'danger_level' => $chemical->danger_level?->value,
            'safety_measures' => $chemical->safety_measures,
            'description' => $chemical->description,
            'image_url' => ImageUploadService::resolveImageUrl($chemical->image_path, $chemical->image_url),
            'is_expired' => $chemical->is_expired,
            'created_at' => $chemical->created_at?->toIso8601String(),
            'updated_at' => $chemical->updated_at?->toIso8601String(),
        ];
    }
}
