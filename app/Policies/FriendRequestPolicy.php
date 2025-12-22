<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\User\FriendRequest;
use App\Models\User\Friendship;

class FriendRequestPolicy
{
    /**
     * Determine if the user can send a friend request to another user.
     */
    public function send(User $user, User $receiver): bool
    {
        // Cannot send to self
        if ($user->id === $receiver->id) {
            return false;
        }

        if ($user->isadmin || $receiver->isadmin) {
            return false;
        }

        // Cannot send if blocked
        if ($user->isblocked || $receiver->isblocked) {
            return false;
        }

        // Cannot send if already friends
        if (Friendship::exists($user->id, $receiver->id)) {
            return false;
        }

        // Cannot send if there's already a pending request
        if ($user->hasSentFriendRequestTo($receiver->id)) {
            return false;
        }

        // Cannot send if the other user has already sent a request
        if ($user->hasReceivedFriendRequestFrom($receiver->id)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can accept a friend request.
     */
    public function accept(User $user, FriendRequest $friendRequest): bool
    {
        // User must be the receiver of the request
        $request = $friendRequest->request;
        $notification = $request->notification;

        if ($notification->receiverid !== $user->id) {
            return false;
        }

        // Request must be pending
        if ($request->status !== 'pending') {
            return false;
        }

        // User cannot be blocked
        if ($user->isblocked) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can reject a friend request.
     */
    public function reject(User $user, FriendRequest $friendRequest): bool
    {
        // Same rules as accept
        return $this->accept($user, $friendRequest);
    }

    /**
     * Determine if the user can cancel a sent friend request.
     */
    public function cancel(User $user, FriendRequest $friendRequest): bool
    {
        // User must be the sender of the request
        $request = $friendRequest->request;

        if ($request->senderid !== $user->id) {
            return false;
        }

        // Request must be pending
        if ($request->status !== 'pending') {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can unfriend another user.
     */
    public function unfriend(User $user, User $friend): bool
    {
        // Must be friends to unfriend
        return Friendship::exists($user->id, $friend->id);
    }
}
