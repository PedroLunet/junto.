<?php

namespace App\Policies;

use App\Models\User;
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

        // if profile is private, check friendship
        $res = DB::selectOne(
            'SELECT fn_are_friends(?,?) as is_friend',
            [$viewer->id, $profileUser->id]
        );
        return $res ? (bool)$res->is_friend : false; 
    }

    // determine if user can view basic profile info
    public function view(User $viewer, User $profileUser): bool
    {
        // anyone can view basic profile info (name, username, etc.)
        return true;
    }
}