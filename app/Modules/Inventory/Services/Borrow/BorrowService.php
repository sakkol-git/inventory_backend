<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Borrow;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\BorrowRecord;

class BorrowService
{
    // This service will handle the business logic for borrowing equipment, chemicals, and plant samples.
    // It will manage the borrow lifecycle, including creating borrow records, approving/rejecting requests,
    // handling returns, and sending notifications for overdue items.
    public function __construct(
        public readonly RequestBorrowService $requestService,
        public readonly ApproveRequestService $approveService,
        public readonly RejectRequestService $rejectService,
        public readonly ReturnEquipmentService $returnService,
    ) {}

    public function requestBorrow(User $user, array $data): BorrowRecord
    {
        return $this->requestService->requestBorrow($user, $data);
    }

    public function approveRequest(User $reviewer, BorrowRecord $record): BorrowRecord
    {
        return $this->approveService->approveBorrow($reviewer, $record);
    }

    public function rejectRequest(User $reviewer, BorrowRecord $record, array $data = []): BorrowRecord
    {
        return $this->rejectService->rejectBorrow($reviewer, $record, $data);
    }

    public function returnItem(User $user, BorrowRecord $record): BorrowRecord
    {
        return $this->returnService->returnItem($user, $record);
    }
}
