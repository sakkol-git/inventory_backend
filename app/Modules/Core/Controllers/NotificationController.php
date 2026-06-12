<?php

declare(strict_types=1);

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UX-05: Notification center API.
 *
 * Leverages Laravel's built-in database notifications (Notifiable trait on User).
 */
class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications — paginated list for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 8), 100);
        $filter = $request->query('filter'); // 'unread' | 'read' | null (all)

        $query = $request->user()->notifications();

        if ($filter === 'unread') {
            $query = $request->user()->unreadNotifications();
        } elseif ($filter === 'read') {
            $query = $request->user()->readNotifications();
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * GET /api/v1/notifications/unread-count — lightweight badge counter.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * POST /api/v1/notifications/{id}/read — mark a single notification read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * POST /api/v1/notifications/read-all — mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * DELETE /api/v1/notifications/{id} — delete a single notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted.',
        ]);
    }
}
