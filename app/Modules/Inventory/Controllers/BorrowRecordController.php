<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Core\Services\Crud\CrudListService;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Requests\BorrowRecord\RejectBorrowRecordRequest;
use App\Modules\Inventory\Requests\BorrowRecord\StoreBorrowRecordRequest;
use App\Modules\Inventory\Resources\BorrowRecordResource;
use App\Modules\Inventory\Services\Borrow\BorrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BorrowRecordController extends Controller
{
    public function __construct(
        public readonly BorrowService $borrowService,
        public readonly CrudListService $crudList
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user('api');

        $query = BorrowRecord::query();

        if ($this->canViewAll($user)) {
            $this->authorize('viewAny', BorrowRecord::class);
        } else {
            $query->where('user_id', $user?->id);
        }

        $records = $this->crudList->listItems(
            $query,
            $request,
            15,
            ['user', 'borrowable', 'reviewer'],
            filterMap: [
                'status' => 'status',
                'borrower_id' => 'user_id',
                'user_id' => 'user_id',
                'borrowable_type' => 'borrowable_type',
                'borrowable_id' => 'borrowable_id',
                'equipment_id' => 'borrowable_id',
            ],
        );

        return BorrowRecordResource::collection($records);
    }

    public function pending(Request $request): AnonymousResourceCollection
    {
        $request->user('api');

        $this->authorize('viewAny', BorrowRecord::class);
        $query = BorrowRecord::query()->pending();

        $records = $this->crudList->listItems(
            $query,
            $request,
            15,
            ['user', 'borrowable', 'reviewer'],
            filterMap: [
                'borrower_id' => 'user_id',
                'user_id' => 'user_id',
                'borrowable_id' => 'borrowable_id',
                'equipment_id' => 'borrowable_id',
            ],
            booleanScopeMap: [
                'pending_only' => 'pending',
            ],
        );

        return BorrowRecordResource::collection($records);
    }

    public function overdue(Request $request): AnonymousResourceCollection
    {
        $request->user('api');

        $this->authorize('viewAny', BorrowRecord::class);
        $query = BorrowRecord::query()->overdue();

        $records = $this->crudList->listItems(
            $query,
            $request,
            15,
            ['user', 'borrowable', 'reviewer'],
            filterMap: [
                'borrower_id' => 'user_id',
                'user_id' => 'user_id',
                'borrowable_id' => 'borrowable_id',
                'equipment_id' => 'borrowable_id',
            ],
            booleanScopeMap: [
                'overdue_only' => 'overdue',
            ],
        );

        return BorrowRecordResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBorrowRecordRequest $request): JsonResponse
    {
        $this->authorize('create', BorrowRecord::class);

        $user = $request->user('api');
        $data = $request->validated();

        $record = $this->canManageBorrows($user)
            ? $this->borrowService->borrow($user, $data)
            : $this->borrowService->requestBorrow($user, $data);

        return (new BorrowRecordResource($record->loadMissing(['user', 'borrowable', 'reviewer'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BorrowRecord $borrowRecord): BorrowRecordResource
    {
        $this->authorize('view', $borrowRecord);

        return new BorrowRecordResource($borrowRecord->loadMissing(['user', 'borrowable', 'reviewer']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function approve(BorrowRecord $borrowRecord): BorrowRecordResource
    {
        $this->authorize('approve', $borrowRecord);

        $record = $this->borrowService->approveRequest(
            auth('api')->user(),
            $borrowRecord
        );

        return new BorrowRecordResource($record->loadMissing(['user', 'borrowable', 'reviewer']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function reject(RejectBorrowRecordRequest $request, BorrowRecord $borrowRecord): BorrowRecordResource
    {
        $this->authorize('reject', $borrowRecord);

        $record = $this->borrowService->rejectRequest(
            auth('api')->user(),
            $borrowRecord,
            $request->validated()
        );

        return new BorrowRecordResource($record->loadMissing(['user', 'borrowable', 'reviewer']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function returnItem(BorrowRecord $borrowRecord): BorrowRecordResource
    {
        $this->authorize('returnItem', $borrowRecord);

        $record = $this->borrowService->returnItem(
            auth('api')->user(),
            $borrowRecord
        );

        return new BorrowRecordResource($record->loadMissing(['user', 'borrowable', 'reviewer']));
    }

    private function canViewAll(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'lab_manager'], 'api')
            || $user->hasPermissionTo('borrows.view', 'api');
    }

    private function canManageBorrows(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'lab_manager'], 'api')
            || $user->hasPermissionTo('borrows.approve', 'api');
    }
}
