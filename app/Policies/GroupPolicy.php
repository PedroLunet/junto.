<?php

namespace App\Policies;

use App\Models\User\User; 
use App\Models\Group;

class GroupPolicy
{

    private function isOwner(User $user, Group $group): bool
    {
        return $group->members()
            ->wherePivot('isowner', true)
            ->where('users.id', $user->id)
            ->exists();
    }

    public function update(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group);
    }

    public function delete(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group);
    }
}