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

        return view('pages.notifications.index', [
            'notifications' => $notifications,
            'friendRequests' => $friendRequests,
            'sentRequests' => $sentRequests,
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

    public function getUnreadCount()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0], 401);
        }

        $count = Notification::where('receiverid', Auth::id())
            ->where('isread', false)
            ->excludeSelfInteractions()
            ->count();

        return response()->json(['count' => $count]);
    }
}
