<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User\User;
use App\Models\User\Friendship;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->senderid || $user->id === $message->receiverid;
    }

    public function send(User $user, User $receiver): bool
    {
        // Cannot send to self
        if ($user->id === $receiver->id) {
            return false;
        }

        // Cannot send if blocked
        if ($user->isblocked || $receiver->isblocked) {
            return false;
        }

        // Must be friends
        return $user->isFriendsWith($receiver->id);
    }


    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->senderid;
    }


    public function deleteConversation(User $user, User $otherUser): bool
    {
        return $user->id !== $otherUser->id;
    }
}
