<?php

namespace App\Services;

use App\Models\User\User;
use App\Models\User\Request;
use Illuminate\Support\Facades\Auth;

class FriendService
{
    /**
     * Get friend button status and data for a specific user.
     *
     * @param User $targetUser The user to check friendship status with
     * @param User|null $currentUser The current authenticated user (null if not authenticated)
     * @return array
     */
    public function getFriendButtonData(User $targetUser, ?User $currentUser = null): array
    {
        if (!$currentUser) {
            $currentUser = Auth::user();
        }

        // If no authenticated user, return guest state
        if (!$currentUser) {
            return [
                'status' => 'guest',
                'show_button' => false,
                'is_self' => false,
                'is_friend' => false,
                'has_sent_request' => false,
                'has_received_request' => false,
                'received_request' => null,
            ];
        }

        $isSelf = $currentUser->id === $targetUser->id;

        // If viewing own profile, no button needed
        if ($isSelf) {
            return [
                'status' => 'self',
                'show_button' => false,
                'is_self' => true,
                'is_friend' => false,
                'has_sent_request' => false,
                'has_received_request' => false,
                'received_request' => null,
            ];
        }

        $isFriend = $currentUser->isFriendsWith($targetUser->id);

        if ($isFriend) {
            return [
                'status' => 'friends',
                'show_button' => true,
                'is_self' => false,
                'is_friend' => true,
                'has_sent_request' => false,
                'has_received_request' => false,
                'received_request' => null,
            ];
        }

        $hasSentRequest = $currentUser->hasSentFriendRequestTo($targetUser->id);

        if ($hasSentRequest) {
            return [
                'status' => 'request_sent',
                'show_button' => true,
                'is_self' => false,
                'is_friend' => false,
                'has_sent_request' => true,
                'has_received_request' => false,
                'received_request' => null,
            ];
        }

        // Check if current user has received request from target user
        $receivedRequest = $this->getReceivedFriendRequest($targetUser->id, $currentUser->id);

        if ($receivedRequest) {
            return [
                'status' => 'request_received',
                'show_button' => true,
                'is_self' => false,
                'is_friend' => false,
                'has_sent_request' => false,
                'has_received_request' => true,
                'received_request' => $receivedRequest,
            ];
        }

        // No relationship - can send friend request
        return [
            'status' => 'can_send_request',
            'show_button' => true,
            'is_self' => false,
            'is_friend' => false,
            'has_sent_request' => false,
            'has_received_request' => false,
            'received_request' => null,
        ];
    }

    /**
     * Get the received friend request from a specific user.
     *
     * @param int $senderId
     * @param int $receiverId
     * @return Request|null
     */
    private function getReceivedFriendRequest(int $senderId, int $receiverId): ?Request
    {
        return Request::where('senderid', $senderId)
            ->whereHas('notification', function ($query) use ($receiverId) {
                $query->where('receiverid', $receiverId);
            })
            ->where('status', 'pending')
            ->whereHas('friendRequest')
            ->first();
    }

    /**
     * Check if two users are friends.
     *
     * @param int $userId1
     * @param int $userId2
     * @return bool
     */
    public function areFriends(int $userId1, int $userId2): bool
    {
        $user1 = User::find($userId1);
        return $user1 ? $user1->isFriendsWith($userId2) : false;
    }

    /**
     * Check if user1 has sent a friend request to user2.
     *
     * @param int $senderId
     * @param int $receiverId
     * @return bool
     */
    public function hasSentFriendRequest(int $senderId, int $receiverId): bool
    {
        $sender = User::find($senderId);
        return $sender ? $sender->hasSentFriendRequestTo($receiverId) : false;
    }

    /**
     * Check if user1 has received a friend request from user2.
     *
     * @param int $receiverId
     * @param int $senderId
     * @return bool
     */
    public function hasReceivedFriendRequest(int $receiverId, int $senderId): bool
    {
        $receiver = User::find($receiverId);
        return $receiver ? $receiver->hasReceivedFriendRequestFrom($senderId) : false;
    }
}
