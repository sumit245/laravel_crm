<?php

namespace App\Http\Controllers;

use App\Models\UserEventNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = max(5, min((int) $request->integer('limit', 15), 50));

        if (!$user || !Schema::hasTable('user_event_notifications')) {
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
            ]);
        }

        $baseQuery = UserEventNotification::query()
            ->where('user_id', $user->id);

        $unreadCount = (clone $baseQuery)
            ->where('is_read', false)
            ->count();

        $items = (clone $baseQuery)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn(UserEventNotification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'module' => $notification->module,
                'action' => $notification->action,
                'is_read' => $notification->is_read,
                'created_at' => optional($notification->created_at)->toIso8601String(),
                'created_at_human' => optional($notification->created_at)->diffForHumans(),
                'payload' => $notification->payload,
            ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ]);
    }

    public function markRead(UserEventNotification $notification): JsonResponse
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 403);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        if (!Schema::hasTable('user_event_notifications')) {
            return response()->json(['ok' => true]);
        }

        UserEventNotification::query()
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }
}
