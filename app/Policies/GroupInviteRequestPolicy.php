<?php

namespace App\Policies;

use App\Models\GroupInviteRequest;
use App\Models\User\User;
use App\Models\Group;
use App\Models\Request as RequestModel;

class GroupInviteRequestPolicy
{
    public function view(User $user, GroupInviteRequest $invite): bool
    {
        $request = RequestModel::find($invite->requestid);
        if (!$request) {
            return false;
        }

        // User is the one invited
        if ($request->senderid === $user->id) {
            return true;
        }

        // User is a group admin
        $group = Group::find($invite->groupid);
        if ($group) {
            return $group->members()
                ->wherePivot('isowner', true)
                ->where('users.id', $user->id)
                ->exists();
        }

        return false;
    }

    public function accept(User $user, GroupInviteRequest $invite): bool
    {
        $request = RequestModel::find($invite->requestid);
        
        // Only the invited user can accept
        return $request && $request->senderid === $user->id;
    }

    public function reject(User $user, GroupInviteRequest $invite): bool
    {
        $request = RequestModel::find($invite->requestid);
        
        // The invited user can reject
        if ($request && $request->senderid === $user->id) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, GroupInviteRequest $invite): bool
    {
        $group = Group::find($invite->groupid);
        if (!$group) {
            return false;
        }

        return $group->members()
            ->wherePivot('isowner', true)
            ->where('users.id', $user->id)
            ->exists();
    }
}
