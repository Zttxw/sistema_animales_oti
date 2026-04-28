<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->when($request->unread, fn($q) => $q->unread())
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function markAsRead(Notification $notification)
    {
        $this->ensureOwnership($notification);

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notificación marcada como leída.']);
    }

    /**
     * Marcar todas como leídas.
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()->unread()->update(['is_read' => true]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas.']);
    }

    /**
     * Total de notificaciones no leídas (para badge en UI).
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()->unread()->count();

        return response()->json(['unread_count' => $count]);
    }

    public function destroy(Notification $notification)
    {
        $this->ensureOwnership($notification);
        $notification->delete();
        return response()->json(['message' => 'Notificación eliminada.']);
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function ensureOwnership(Notification $notification): void
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para esta notificación.');
        }
    }
}