<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Modules\Core\Http\Controllers\Controller;

use App\Modules\Inventory\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * GET /api/dashboard
     *
     * Thin dispatcher — all logic lives in DashboardService (cached).
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'counts' => $this->dashboardService->getCounts(),
                'alerts' => $this->dashboardService->getAlerts(),
                'recent_activity' => $this->dashboardService->getRecentActivity(),
                'status_breakdown' => $this->dashboardService->getStatusBreakdown(),
            ],
        ]);
    }
}
