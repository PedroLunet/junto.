<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\User;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendRequestController extends Controller
{
    /**
     * Display a listing of received friend requests for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        // Get pending friend requests
        $friendRequests = FriendRequest::whereHas('request.notification', function ($query) use ($user) {
            $query->where('receiverid', $user->id);
        })
            ->whereHas('request', function ($query) {
                $query->where('status', 'pending');
            })
            ->with(['request.sender', 'request.notification'])
            ->get();

        return view('friend-requests.index', compact('friendRequests'));
    }

    /**
     * Display a listing of sent friend requests for the authenticated user.
     */
    public function sent()
    {
        $user = Auth::user();

        $sentRequests = FriendRequest::whereHas('request', function ($query) use ($user) {
            $query->where('senderid', $user->id)
                ->where('status', 'pending');
        })
            ->with(['request.notification'])
            ->get();

        return view('friend-requests.sent', compact('sentRequests'));
    }

    /**
     * Send a friend request to another user.
     */
    public function store(HttpRequest $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
        ]);

        $senderId = Auth::id();
        $receiverId = $validated['receiver_id'];

        // Check if trying to send to self
        if ($senderId === $receiverId) {
            return back()->with('error', 'You cannot send a friend request to yourself.');
        }

        // Check if already friends
        if (Friendship::exists($senderId, $receiverId)) {
            return back()->with('error', 'You are already friends with this user.');
        }

        // Check if there's already a pending request
        $existingRequest = Request::where('senderid', $senderId)
            ->whereHas('notification', function ($query) use ($receiverId) {
                $query->where('receiverid', $receiverId);
            })
            ->whereHas('friendRequest')
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You have already sent a friend request to this user.');
        }

        // Check if there's a pending request from the other user
        $reverseRequest = Request::where('senderid', $receiverId)
            ->whereHas('notification', function ($query) use ($senderId) {
                $query->where('receiverid', $senderId);
            })
            ->whereHas('friendRequest')
            ->where('status', 'pending')
            ->first();

        if ($reverseRequest) {
            return back()->with('error', 'This user has already sent you a friend request. Please accept it instead.');
        }

        try {
            // Send the friend request
            FriendRequest::send($senderId, $receiverId);

            return back()->with('success', 'Friend request sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send friend request. Please try again.');
        }
    }

    /**
     * Accept a friend request.
     */
    public function accept($requestId)
    {
        $user = Auth::user();

        // Find the friend request
        $friendRequest = FriendRequest::where('requestid', $requestId)
            ->whereHas('request.notification', function ($query) use ($user) {
                $query->where('receiverid', $user->id);
            })
            ->whereHas('request', function ($query) {
                $query->where('status', 'pending');
            })
            ->firstOrFail();

        try {
            $friendRequest->accept();

            return back()->with('success', 'Friend request accepted!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to accept friend request. Please try again.');
        }
    }

    /**
     * Reject a friend request.
     */
    public function reject($requestId)
    {
        $user = Auth::user();

        // Find the friend request
        $friendRequest = FriendRequest::where('requestid', $requestId)
            ->whereHas('request.notification', function ($query) use ($user) {
                $query->where('receiverid', $user->id);
            })
            ->whereHas('request', function ($query) {
                $query->where('status', 'pending');
            })
            ->firstOrFail();

        try {
            $friendRequest->reject();

            return back()->with('success', 'Friend request rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject friend request. Please try again.');
        }
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancel($requestId)
    {
        $user = Auth::user();

        // Find the friend request sent by the authenticated user
        $friendRequest = FriendRequest::where('requestid', $requestId)
            ->whereHas('request', function ($query) use ($user) {
                $query->where('senderid', $user->id)
                    ->where('status', 'pending');
            })
            ->firstOrFail();

        try {
            $friendRequest->reject(); // Rejecting cancels it

            return back()->with('success', 'Friend request cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel friend request. Please try again.');
        }
    }

    /**
     * Remove a friend (unfriend).
     */
    public function unfriend($userId)
    {
        $currentUser = Auth::user();

        // Verify the users are actually friends
        if (!Friendship::exists($currentUser->id, $userId)) {
            return back()->with('error', 'You are not friends with this user.');
        }

        try {
            Friendship::unfriend($currentUser->id, $userId);

            return back()->with('success', 'Friend removed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove friend. Please try again.');
        }
    }

    // display a list of the authenticated user's friends
    public function friends()
    {
        $user = Auth::user();

        // get friends manually
        $friendsAsUser1 = User::join('friendship', 'users.id', '=', 'friendship.userid2')
            ->where('friendship.userid1', $user->id)
            ->select('users.*')
            ->get();

        $friendsAsUser2 = User::join('friendship', 'users.id', '=', 'friendship.userid1')
            ->where('friendship.userid2', $user->id)
            ->select('users.*')
            ->get();

        $friends = $friendsAsUser1->merge($friendsAsUser2);

        return view('friends.index', compact('friends', 'user'));
    }

    // display a list of friends for a specific user by username
    public function friendsByUsername($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        // get friends manually
        $friendsAsUser1 = User::join('friendship', 'users.id', '=', 'friendship.userid2')
            ->where('friendship.userid1', $user->id)
            ->select('users.*')
            ->get();

        $friendsAsUser2 = User::join('friendship', 'users.id', '=', 'friendship.userid1')
            ->where('friendship.userid2', $user->id)
            ->select('users.*')
            ->get();

        $friends = $friendsAsUser1->merge($friendsAsUser2);

        return view('friends.index', compact('friends', 'user'));
    }
}
