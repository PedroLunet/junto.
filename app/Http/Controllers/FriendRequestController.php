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

    $receiver = User::findOrFail($validated['receiver_id']);
    $this->authorize('send', [FriendRequest::class, $receiver]);

    try {
      // Send the friend request
      FriendRequest::send(Auth::id(), $receiver->id);

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
    $friendRequest = FriendRequest::with('request.notification')->findOrFail($requestId);
    $this->authorize('accept', $friendRequest);

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
    $friendRequest = FriendRequest::with('request.notification')->findOrFail($requestId);
    $this->authorize('reject', $friendRequest);

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
    $friendRequest = FriendRequest::with('request')->findOrFail($requestId);
    $this->authorize('cancel', $friendRequest);

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
    $friend = User::findOrFail($userId);
    $this->authorize('unfriend', [FriendRequest::class, $friend]);

    try {
      Friendship::unfriend(Auth::id(), $userId);

      return back()->with('success', 'Friend removed successfully.');
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to remove friend. Please try again.');
    }
  }

  /**
   * Display a list of the authenticated user's friends.
   */
  public function friends()
  {
    $user = Auth::user();

    // Get friends manually since we need both directions
    $friendsAsUser1 = User::join('friendship', 'users.id', '=', 'friendship.userid2')
      ->where('friendship.userid1', $user->id)
      ->select('users.*')
      ->get();

    $friendsAsUser2 = User::join('friendship', 'users.id', '=', 'friendship.userid1')
      ->where('friendship.userid2', $user->id)
      ->select('users.*')
      ->get();

    $friends = $friendsAsUser1->merge($friendsAsUser2);

    return view('friends.index', compact('friends'));
  }
}
