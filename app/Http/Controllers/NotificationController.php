<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display the full notification center view
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $category = $request->query('category', 'all');

        $query = $user->notifications();

        if ($category !== 'all') {
            $query->where('data->category', $category);
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'category'));
    }

    /**
     * Get unread count and latest notifications for header dropdown polling
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()->latest()->take(6)->get()->map(function ($notification) {
            $data = $notification->data;
            return [
                'id'         => $notification->id,
                'title'      => $data['title'] ?? 'Pemberitahuan',
                'message'    => $data['message'] ?? '',
                'category'   => $data['category'] ?? 'academic',
                'action_url' => $data['action_url'] ?? null,
                'icon'       => $data['icon'] ?? 'bx-bell',
                'read_at'    => $notification->read_at ? $notification->read_at->toIso8601String() : null,
                'time_ago'   => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $recent,
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        $actionUrl = $notification->data['action_url'] ?? null;
        if ($actionUrl && filter_var($actionUrl, FILTER_VALIDATE_URL)) {
            return redirect($actionUrl);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $user = Auth::user();
        $user->readNotifications()->delete();

        return back()->with('success', 'Riwayat notifikasi yang sudah dibaca berhasil dibersihkan.');
    }
}
