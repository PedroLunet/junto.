<?php

namespace App\Http\Controllers\Notification;

use App\Models\User\Notification;
use App\Models\User\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        
        $notifications = Notification::where('receiverid', Auth::id())
            ->with(['tagNotification.tagger'])
            ->excludeSelfInteractions()
            ->orderBy('createdat', 'desc')
            ->paginate(15);

        $friendRequests = FriendRequest::whereHas('request.notification', function ($query) use ($user) {
            $query->where('receiverid', $user->id);
        })
            ->whereHas('request', function ($query) {
                $query->where('status', 'pending');
            })
            ->whereHas('request.sender', function ($query) {
                $query->where('isdeleted', false);
            })
            ->with(['request.sender', 'request.notification'])
            ->get();

        $sentRequests = FriendRequest::whereHas('request', function ($query) use ($user) {
            $query->where('senderid', $user->id)
                ->where('status', 'pending');
        })
            ->with(['request.notification'])
            ->get();

        $groupNotifications = Notification::where('receiverid', Auth::id())
            ->where(function ($query) {
                $query->whereHas('groupInviteRequest')
                      ->orWhereHas('groupJoinRequest');
            })
            ->with(['groupInviteRequest.group', 'groupInviteRequest.request', 'groupJoinRequest'])
            ->orderBy('createdat', 'desc')
            ->paginate(15);

        return view('pages.notifications.index', [
            'notifications' => $notifications,
            'friendRequests' => $friendRequests,
            'sentRequests' => $sentRequests,
            'groupNotifications' => $groupNotifications,
            'pageTitle' => 'Inbox'
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

    public function snoozeNotifications(Request $request)
    {
        $validated = $request->validate([
            'duration' => 'required|integer|min:1|max:10080',
        ]);

        $snoozedUntil = now()->addMinutes($validated['duration']);
        
        session(['notifications_snoozed_until' => $snoozedUntil]);

        return response()->json(['success' => true, 'snoozed_until' => $snoozedUntil]);
    }

    public function clearSnooze()
    {
        session()->forget('notifications_snoozed_until');
        
        return response()->json(['success' => true]);
    }

    public function getSnoozeStatus()
    {
        if (!Auth::check()) {
            return response()->json(['snoozed' => false], 401);
        }

        $snoozedUntil = session('notifications_snoozed_until');
        $isSnoozed = $snoozedUntil && $snoozedUntil > now();

        return response()->json([
            'snoozed' => $isSnoozed,
            'snoozed_until' => $isSnoozed ? $snoozedUntil : null,
        ]);
    }

    public function getUnreadCount()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0], 401);
        }

        $snoozedUntil = session('notifications_snoozed_until');
        if ($snoozedUntil && $snoozedUntil > now()) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('receiverid', Auth::id())
            ->where('isread', false)
            ->excludeSelfInteractions()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getLatestUnread()
    {
        if (!Auth::check()) {
            return response()->json(['notification' => null], 401);
        }

        $snoozedUntil = session('notifications_snoozed_until');
        if ($snoozedUntil && $snoozedUntil > now()) {
            return response()->json(['notification' => null]);
        }

        $notification = Notification::where('receiverid', Auth::id())
            ->where('isread', false)
            ->excludeSelfInteractions()
            ->orderBy('createdat', 'desc')
            ->first();

        if (!$notification) {
            return response()->json(['notification' => null]);
        }

        return response()->json(['notification' => [
            'id' => $notification->id,
            'message' => $notification->message,
        ]]);
    }
}
