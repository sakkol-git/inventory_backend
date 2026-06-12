<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Notification\BorrowRecord;

use App\Modules\Inventory\Models\BorrowRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestBorrowNotification extends Notification
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
            'type' => 'request_borrow',
            'record_id' => $this->record->id,
            'borrowable_name' => $itemName,
            'requested_at' => $this->record->created_at?->toISOString(),
            'due_at' => $this->record->due_at?->toISOString(),
            'reviewer_name' => $this->record->reviewer?->name,
            'message' => "New borrow request submitted for {$itemName}.",
        ];
    }
}
