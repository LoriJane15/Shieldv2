<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', ['notifications' => $request->user()->notifications()->paginate(20)]);
    }

    public function status(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'unread_ids' => $request->user()->unreadNotifications()->latest()->limit(100)->pluck('id'),
            'latest_id' => (clone $notifications)->latest()->value('id'),
            'checked_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'All notifications marked as read.');
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->notifiable_id === $request->user()->id && $notification->notifiable_type === get_class($request->user()), 403);
        $notification->markAsRead();

        return redirect()->route('notifications.index');
    }
}
