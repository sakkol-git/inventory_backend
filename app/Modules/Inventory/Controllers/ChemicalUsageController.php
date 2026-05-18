<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Modules\Core\Concerns\EscapesSearchTerm;
use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Inventory\Models\ChemicalUsageLog;
use App\Modules\Inventory\Requests\Chemical\StoreChemicalUsageLogRequest;
use App\Modules\Inventory\Resources\ChemicalUsageLogResource;
use App\Modules\Inventory\Services\ChemicalUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChemicalUsageController extends Controller
{
    use EscapesSearchTerm;

    public function __construct(
        private readonly ChemicalUsageService $chemicalUsageService,
    ) {}

    /**
     * GET /api/chemical-usage-logs
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ChemicalUsageLog::class);

        $query = ChemicalUsageLog::with(['chemical', 'user'])->latest('used_at');

        if ($request->filled('chemical_id')) {
            $query->forChemical($request->integer('chemical_id'));
        }
        if ($request->filled('user_id')) {
            $query->forUser($request->integer('user_id'));
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->betweenDates($request->input('from'), $request->input('to'));
        }
        if ($request->filled('purpose')) {
            $term = $this->escapeLike($request->input('purpose'));
            $query->where('purpose', 'like', "%{$term}%");
        }

        return ChemicalUsageLogResource::collection($query->paginate(15));
    }

    /**
     * POST /api/chemical-usage-logs
     */
    public function store(StoreChemicalUsageLogRequest $request): JsonResponse
    {
        $this->authorize('create', ChemicalUsageLog::class);

        $log = $this->chemicalUsageService->create(
            data: $request->validated(),
            user: $request->user(),
        );

        $log->load(['chemical', 'user']);

        return (new ChemicalUsageLogResource($log))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/chemical-usage-logs/{chemicalUsageLog}
     */
    public function show(ChemicalUsageLog $chemicalUsageLog): ChemicalUsageLogResource
    {
        $this->authorize('view', $chemicalUsageLog);

        $chemicalUsageLog->load(['chemical', 'user']);

        return new ChemicalUsageLogResource($chemicalUsageLog);
    }
}
