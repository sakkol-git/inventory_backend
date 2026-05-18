<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Resources\UserResource;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantStock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BorrowRecord
 */
class BorrowRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var BorrowRecord $record */
        $record = $this->resource;

        return [
            'id' => $record->id,
            'borrowable_type' => $record->borrowable_type,
            'borrowable_id' => $record->borrowable_id,
            'status' => $record->status?->value,
            'quantity' => $record->quantity,
            'borrowed_at' => $record->borrowed_at?->toIso8601String(),
            'due_at' => $record->due_at?->toIso8601String(),
            'returned_at' => $record->returned_at?->toIso8601String(),
            'notes' => $record->notes,
            'reviewed_at' => $record->reviewed_at?->toIso8601String(),
            'rejected_reason' => $record->rejected_reason,
            'created_at' => $record->created_at?->toIso8601String(),
            'updated_at' => $record->updated_at?->toIso8601String(),

            // Computed flags — avoids repeated logic in the frontend
            'is_overdue' => $record->status === BorrowStatus::OVERDUE
                || ($record->due_at && $record->due_at->isPast() && ! $record->returned_at),
            'is_active' => in_array($record->status, [
                BorrowStatus::APPROVED,
                BorrowStatus::BORROWED,
                BorrowStatus::OVERDUE,
            ], true),
            'days_overdue' => $record->due_at && $record->due_at->isPast()
                ? (int) $record->due_at->diffInDays(now())
                : null,

            // Relationships (only included when eager-loaded)
            'borrowable' => $this->resolveBorrowableResource(),
            'borrower' => UserResource::make($this->whenLoaded('user')),
            'reviewer' => UserResource::make($this->whenLoaded('reviewer')),
        ];
    }

    private function resolveBorrowableResource(): ?JsonResource
    {
        if (! $this->relationLoaded('borrowable')) {
            return null;
        }

        $borrowable = $this->borrowable;

        return match (true) {
            $borrowable instanceof Equipment => new EquipmentResource($borrowable),
            $borrowable instanceof Chemical => new ChemicalResource($borrowable),
            $borrowable instanceof PlantStock => new PlantStockResource($borrowable),
            $borrowable instanceof PlantSample => new PlantSampleResource($borrowable),
            default => null,
        };
    }
}
