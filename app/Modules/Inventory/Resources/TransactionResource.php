<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action?->value,
            'quantity' => $this->quantity,
            'note' => $this->note,
            'user' => [
                'id' => $this->whenLoaded('user', fn () => $this->user->id),
                'name' => $this->whenLoaded('user', fn () => $this->user->name),
            ],
            'item' => [
                'type' => $this->transactionable_type,
                'id' => $this->transactionable_id,
                'data' => $this->whenLoaded('transactionable'),
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
