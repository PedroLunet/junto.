<?php

namespace App\Policies;

use App\Models\Post\Post;
use App\Models\User\User;
use App\Models\Friendship;

class PostPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // If the user is the author, they can view it.
        if ($user->id === $post->userid) {
            return true;
        }

        // Get the author of the post
        $author = $post->user;

        // If author is not found, deny
        if (!$author) {
            return false;
        }

        // If the author's profile is public, anyone can view.
        if (!$author->isprivate) {
            return true;
        }

        // If private, check if the viewer is a friend of the author.
        return Friendship::exists($user->id, $author->id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->userid;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->userid || $user->isadmin;
    }
}
