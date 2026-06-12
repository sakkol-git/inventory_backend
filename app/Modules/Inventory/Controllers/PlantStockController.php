<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Modules\Core\Contracts\ICrudService;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Inventory\Models\PlantStock;
use App\Modules\Inventory\Requests\Stock\StorePlantStockRequest;
use App\Modules\Inventory\Requests\Stock\UpdatePlantStockRequest;
use App\Modules\Inventory\Resources\PlantStockResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlantStockController extends Controller
{
    public function __construct(
        private readonly ICrudService $crudService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PlantStock::class);
        $stocks = $this->crudService->listItems(
            modelOrQuery: PlantStock::class,
            request: $request,
            perPage: 10,
            with: ['sample'],
            filterMap: [
                'sample_id' => 'plant_sample_id',
                'status' => 'status',
            ],
        );

        return PlantStockResource::collection($stocks);
    }

    public function store(StorePlantStockRequest $request): JsonResponse
    {
        $this->authorize('create', PlantStock::class);

        $stock = $this->crudService->create(
            modelClass: PlantStock::class,
            data: $request->validated(),
            user: auth('api')->user(),
        );

        $stock->load('sample');

        return (new PlantStockResource($stock))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PlantStock $plantStock): PlantStockResource
    {
        $this->authorize('view', $plantStock);

        $plantStock->load('sample');

        return new PlantStockResource($plantStock);
    }

    public function update(UpdatePlantStockRequest $request, PlantStock $plantStock): PlantStockResource
    {
        $this->authorize('update', $plantStock);

        $data = $request->validated();

        // ── Inventory Guard ─────────────────────────────────────────────────
        // When only one of the two fields is sent, cross-check against the
        // persisted value to ensure reserved never exceeds total quantity.
        $plantStock = \Illuminate\Support\Facades\DB::transaction(function () use ($plantStock, $data) {
            $lockedStock = PlantStock::lockForUpdate()->findOrFail($plantStock->id);
            
            $newQuantity = (int) ($data['quantity'] ?? $lockedStock->quantity);
            $newReservedQuantity = (int) ($data['reserved_quantity'] ?? $lockedStock->reserved_quantity);

            if ($newReservedQuantity > $newQuantity) {
                abort(422, 'Reserved quantity cannot exceed the total quantity.');
            }

            return $this->crudService->update(
                instance: $lockedStock,
                data: $data,
                user: auth('api')->user(),
            );
        });

        $plantStock->load('sample');

        return new PlantStockResource($plantStock);
    }

    public function destroy(PlantStock $plantStock): JsonResponse
    {
        $this->authorize('delete', $plantStock);

        $this->crudService->delete(
            instance: $plantStock,
            user: auth('api')->user(),
        );

        return response()->json(['message' => 'Stock record deleted successfully.']);
    }
}
