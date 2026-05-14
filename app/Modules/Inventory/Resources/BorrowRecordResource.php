<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use App\Modules\Core\Resources\UserResource;
use App\Modules\Inventory\Enums\BorrowStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'borrowable_type' => $this->borrowable_type,
            'borrowable_id' => $this->borrowable_id,
            'status' => $this->status?->value,
            'quantity' => $this->quantity,
            'borrowed_at' => $this->borrowed_at?->toIso8601String(),
            'due_at' => $this->due_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'notes' => $this->notes,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rejected_reason' => $this->rejected_reason,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Computed flags — avoids repeated logic in the frontend
            'is_overdue' => $this->status === BorrowStatus::OVERDUE
                || ($this->due_at && $this->due_at->isPast() && ! $this->returned_at),
            'is_active' => in_array($this->status, [
                BorrowStatus::APPROVED,
                BorrowStatus::BORROWED,
                BorrowStatus::OVERDUE,
            ], true),
            'days_overdue' => $this->due_at && $this->due_at->isPast()
                ? (int) $this->due_at->diffInDays(now())
                : null,

            // Relationships (only included when eager-loaded)
            'borrowable' => EquipmentResource::make($this->whenLoaded('borrowable')),
            'borrower' => UserResource::make($this->whenLoaded('user')),
            'reviewer' => UserResource::make($this->whenLoaded('reviewer')),
        ];
    }
}
