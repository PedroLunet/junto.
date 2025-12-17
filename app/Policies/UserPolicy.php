<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\User\Friendship;
use Illuminate\Support\Facades\DB;

class UserPolicy
{
    // determine if the user can view the profile's posts and content
    public function viewPosts(User $viewer, User $profileUser)
    {
        // user can always view their own posts
        if ($viewer->id === $profileUser->id) {
            return true;
        }

        // if profile is not private, anyone can view
        if (!$profileUser->isprivate) {
            return true;
        }

        // if profile is private, check friendship using Eloquent
        return Friendship::exists($viewer->id, $profileUser->id);
    }

    // determine if user can view basic profile info
    public function view(User $viewer, User $profileUser): bool
    {
        // anyone can view basic profile info (name, username, etc.)
        return true;
    }

    // determine if user can update the profile
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
