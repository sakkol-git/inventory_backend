<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Inventory\Models\PlantSample;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Core\Resources\UserResource;
use App\Modules\Core\Services\ImageUpload\ImageUploadService;

/**
 * @mixin PlantSample
 */
class PlantSampleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var PlantSample $sample */
        $sample = $this->resource;

        /** @return array<string, mixed> */
        return [
            'id' => $sample->id,
            'sample_name' => $sample->sample_name,
            'sample_code' => $sample->sample_code,
            'status' => $sample->status?->value,
            'owner_name' => $sample->owner_name,
            'department' => $sample->department,
            'origin_location' => $sample->origin_location,
            'quantity' => $sample->stock_quantity,
            'brought_at' => $sample->brought_at?->toIso8601String(),
            'lab_location' => $sample->lab_location?->value,
            'description' => $sample->description,
            'image_url' => ImageUploadService::resolveImageUrl($sample->image_path, $sample->image_url),
            'plant_variety' => new PlantVarietyResource($this->whenLoaded('plantVariety')),
            'contributor' => new UserResource($this->whenLoaded('contributor')),
            'created_at' => $sample->created_at?->toIso8601String(),
            'updated_at' => $sample->updated_at?->toIso8601String(),
        ];
    }
}
