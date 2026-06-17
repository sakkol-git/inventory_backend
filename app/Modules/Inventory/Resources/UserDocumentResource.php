<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Inventory\Resources\AchievementResource;

class UserDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'description' => $this->description,
            'status' => $this->status,
            'download_url' => route('user-documents.download', ['userDocument' => $this->id]),
            'achievement_id' => $this->achievement_id,
            'user' => [
                'id' => $this->whenLoaded('user', fn () => $this->user->id),
                'name' => $this->whenLoaded('user', fn () => $this->user->name),
            ],
            'achievement' => new AchievementResource($this->whenLoaded('achievement')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
