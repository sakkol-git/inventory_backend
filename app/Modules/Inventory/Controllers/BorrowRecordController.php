<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
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
        $this->authorize('viewAny', BorrowRecord::class);

        $records = $this->crudList->listItems(
            BorrowRecord::class,
            $request,
            15,
            ['user', 'borrowable', 'reviewer'],
            filterMap: [
                'status' => 'status',
                'borrower_id' => 'user_id',
                'user_id' => 'user_id',
                'borrowable_id' => 'borrowable_id',
                'equipment_id' => 'borrowable_id',
            ],
        );

        return BorrowRecordResource::collection($records);
    }

    public function pending(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', BorrowRecord::class);
        $request->merge(['pending_only' => true]);

        $records = $this->crudList->listItems(
            BorrowRecord::class,
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
        $this->authorize('viewAny', BorrowRecord::class);
        $request->merge(['overdue_only' => true]);

        $records = $this->crudList->listItems(
            BorrowRecord::class,
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

        $record = $this->borrowService->requestBorrow(
            $request->user('api'),
            $request->validated()
        );

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
}
