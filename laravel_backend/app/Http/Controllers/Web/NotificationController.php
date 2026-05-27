<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // GET /api/user/notifications
    public function index()
    {
        $userId = (string) Auth::id();

        $notifications = Notification::where(function ($q) use ($userId) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $userId);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($n) use ($userId) {
                return [
                    'id'        => (string) $n->_id,
                    'title'     => $n->title,
                    'body'      => $n->body,
                    'type'      => $n->type,
                    'commodity' => $n->commodity,
                    'meta'      => $n->meta ?? [],
                    'isRead'    => in_array($userId, $n->is_read_by ?? []),
                    'time'      => $n->created_at?->diffForHumans() ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    // POST /api/user/notifications/{id}/read
    public function markRead(string $id)
    {
        $userId = (string) Auth::id();
        $notif  = Notification::findOrFail($id);

        $readBy = $notif->is_read_by ?? [];
        if (!in_array($userId, $readBy)) {
            $readBy[] = $userId;
            $notif->is_read_by = $readBy;
            $notif->save();
        }

        return response()->json(['success' => true]);
    }

    // POST /api/user/notifications/read-all
    public function markAllRead()
    {
        $userId = (string) Auth::id();

        Notification::where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        })
        ->get()
        ->each(function ($n) use ($userId) {
            $readBy = $n->is_read_by ?? [];
            if (!in_array($userId, $readBy)) {
                $readBy[]      = $userId;
                $n->is_read_by = $readBy;
                $n->save();
            }
        });

        return response()->json(['success' => true]);
    }
}