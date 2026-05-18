<?php

declare(strict_types=1);

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

/**
 * MF-01: Audit trail viewer — browse activity logs from the frontend.
 */
class ActivityLogController extends Controller
{
    /**
     * GET /api/v1/activity-logs — paginated, filterable activity log.
     *
     * Query params:
     *  - causer_id   (int)    — filter by user who performed the action
     *  - subject_type (string) — morph alias, e.g. "plant_species"
     *  - subject_id   (int)   — filter to a specific record
     *  - event        (string) — created|updated|deleted
     *  - from         (date)  — start date (inclusive)
     *  - to           (date)  — end date (inclusive)
     *  - per_page     (int)   — pagination size (max 50)
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view-reports');

        $request->validate([
            'causer_id' => ['integer', 'exists:users,id'],
            'subject_type' => ['string', 'max:100'],
            'subject_id' => ['integer'],
            'event' => ['string', 'in:created,updated,deleted'],
            'from' => ['date'],
            'to' => ['date'],
            'per_page' => ['integer', 'min:1', 'max:50'],
        ]);

        $perPage = min((int) $request->query('per_page', '20'), 50);

        $query = Activity::query()
            ->with('causer:id,name,email')
            ->latest();

        if ($causerId = $request->query('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        if ($subjectType = $request->query('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($subjectId = $request->query('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => collect($logs->items())->map(fn (Activity $log) => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'event' => $log->event,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'causer' => $log->causer ? [
                    'id' => $log->causer->id,
                    'name' => $log->causer->name,
                    'email' => $log->causer->email,
                ] : null,
                'properties' => $log->properties,
                'created_at' => $log->created_at->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/activity-logs/{id} — single log entry.
     */
    public function show(int $id): JsonResponse
    {
        Gate::authorize('view-reports');

        $log = Activity::with('causer:id,name,email')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'event' => $log->event,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'causer' => $log->causer ? [
                    'id' => $log->causer->id,
                    'name' => $log->causer->name,
                    'email' => $log->causer->email,
                ] : null,
                'properties' => $log->properties,
                'created_at' => $log->created_at->toIso8601String(),
            ],
        ]);
    }
}
