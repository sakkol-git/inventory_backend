<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Notification\BorrowRecord;

use App\Modules\Inventory\Models\BorrowRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReturnItemNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly BorrowRecord $record
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $itemName = $this->record->borrowable?->equipment_name
            ?? $this->record->borrowable?->name
            ?? 'item';

        return [
            'type' => 'return_item',
            'record_id' => $this->record->id,
            'borrowable_name' => $itemName,
            'borrowed_at' => $this->record->borrowed_at?->toISOString(),
            'returned_at' => $this->record->returned_at?->toISOString(),
            'reviewer_name' => $this->record->reviewer?->name,
            'message' => "The item {$itemName} has been returned.",
        ];
    }
}
