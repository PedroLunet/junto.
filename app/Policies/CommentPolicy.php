<?php

namespace App\Policies;

use App\Models\Post\Comment;
use App\Models\User\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->userid || $user->isadmin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // User can delete their own comment, admins can delete any comment,
        // and post owners can delete comments on their posts.
        return $user->id === $comment->userid ||
            $user->isadmin ||
            $user->id === $comment->post->userid;
    }
}
