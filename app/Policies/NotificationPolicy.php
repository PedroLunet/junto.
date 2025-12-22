<?php

namespace App\Policies;

use App\Models\User\Notification;
use App\Models\User\User;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->receiverid;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $user->id === $notification->receiverid;
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->receiverid;
    }
}
