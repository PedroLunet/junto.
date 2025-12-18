<?php

namespace App\Http\Controllers\Notification;

use App\Models\User\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::where('receiverid', Auth::id())
            ->orderBy('createdat', 'desc')
            ->paginate(15);

        return view('pages.notifications.index', [
            'notifications' => $notifications,
            'pageTitle' => 'Notifications'
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        if ($notification->receiverid !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('receiverid', Auth::id())
            ->where('isread', false)
            ->update(['isread' => true]);

        return response()->json(['success' => true]);
    }

    public function snooze($id, Request $request)
    {
        $request->validate([
            'duration' => 'required|integer|in:0,30,60,480,1440'
        ]);

        $notification = Notification::findOrFail($id);
        
        if ($notification->receiverid !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Snooze functionality will be available after migration
        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0], 401);
        }

        $count = Notification::where('receiverid', Auth::id())
            ->where('isread', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
