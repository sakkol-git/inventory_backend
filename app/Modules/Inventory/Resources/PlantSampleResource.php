<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Resources\UserResource;
use App\Modules\Core\Services\ImageUpload\ImageUploadService;
use App\Modules\Inventory\Models\PlantSample;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Resources\Json\JsonResource;

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

            'plant_variety_id' => $sample->plant_variety_id,
            'user_id' => $sample->user_id,

            'identity' => [
                'name' => $sample->sample_name,
                'code' => $sample->sample_code,
                'status' => $sample->status?->value,
            ],

            'relationships' => [
                'variety' => new PlantVarietyResource($this->whenLoaded('plantVariety')),
                'contributor' => new UserResource($this->whenLoaded('contributor')),
                'stocks' => $this->resource->relationLoaded('stocks')
                    ? PlantStockResource::collection($this->resource->getRelation('stocks'))
                    : new MissingValue,
            ],

            'details' => [
                'owner' => data_get($sample->relationLoaded('contributor') ? $sample->getRelation('contributor') : null, 'name'),
                'department' => $sample->department,
                'origin' => $sample->origin_location,
                'quantity' => $sample->stock_quantity,
            ],

            'lab_info' => [
                'brought_at' => $sample->brought_at?->toISOString(),
                'location' => $sample->lab_location?->value,
            ],

            'meta' => [
                'description' => $sample->description,
                'image' => ImageUploadService::resolveImageUrl($sample->image_path, $sample->image_url),
                'created_at' => $sample->created_at?->toISOString(),
                'updated_at' => $sample->updated_at?->toISOString(),
            ],
        ];
    }
}
