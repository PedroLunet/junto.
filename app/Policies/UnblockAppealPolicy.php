<?php

namespace App\Policies;

use App\Models\UnblockAppeal;
use App\Models\User\User;

class UnblockAppealPolicy
{
    public function view(User $user, UnblockAppeal $appeal): bool
    {
        // Admins can view all appeals
        if ($user->isadmin) {
            return true;
        }

        // Users can view their own appeals
        return $user->id === $appeal->userid;
    }


    public function create(User $user): bool
    {
        // Only blocked users can create appeals
        return $user->isblocked;
    }

    public function update(User $user, UnblockAppeal $appeal): bool
    {
        // Only admins can update appeals (approve/reject)
        return $user->isadmin;
    }
}
