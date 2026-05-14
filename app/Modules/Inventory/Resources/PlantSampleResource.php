<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Services\ImageUpload\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Inventory\Models\PlantSample
 */
class PlantSampleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var \App\Modules\Inventory\Models\PlantSample $sample */
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
            'quantity' => $sample->quantity,
            'brought_at' => $sample->brought_at?->toIso8601String(),
            'lab_location' => $sample->lab_location,
            'description' => $sample->description,
            'image_url' => $sample->image_url,
            'created_at' => $sample->created_at?->toIso8601String(),
            'updated_at' => $sample->updated_at?->toIso8601String(),
        ];
    }
}
