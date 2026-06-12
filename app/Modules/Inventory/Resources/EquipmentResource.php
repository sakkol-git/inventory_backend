<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Services\ImageUpload\ImageUploadService;
use App\Modules\Inventory\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Equipment
 */
class EquipmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Equipment $equipment */
        $equipment = $this->resource;

        /** @return array<string, mixed> */
        return [
            'id' => $equipment->id,
            'equipment_name' => $equipment->equipment_name,
            'equipment_code' => $equipment->equipment_code,
            'category' => $equipment->category?->value,
            'status' => $equipment->status?->value,
            'condition' => $equipment->condition?->value,
            'location' => $equipment->location,
            'manufacturer' => $equipment->manufacturer,
            'model_name' => $equipment->model_name,
            'serial_number' => $equipment->serial_number,
            'purchase_date' => $equipment->purchase_date?->format('Y-m-d'),
            'purchase_price' => $equipment->purchase_price,
            'description' => $equipment->description,
            'image_url' => ImageUploadService::resolveImageUrl($equipment->image_path, $equipment->image_url),
            'is_borrowable' => $equipment->is_borrowable,
            'created_at' => $equipment->created_at?->toISOString(),
            'updated_at' => $equipment->updated_at?->toISOString(),
        ];
    }
}
