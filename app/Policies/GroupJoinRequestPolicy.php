<?php

namespace App\Policies;

use App\Models\GroupJoinRequest;
use App\Models\User\User;
use App\Models\Group;
use App\Models\Request as RequestModel;

class GroupJoinRequestPolicy
{
    public function view(User $user, GroupJoinRequest $groupJoinRequest): bool
    {
        // User is the sender
        $request = RequestModel::find($groupJoinRequest->requestid);
        if ($request && $request->senderid === $user->id) {
            return true;
        }

        // User is a group admin/owner
        $group = Group::find($groupJoinRequest->groupid);
        if ($group) {
            return $group->members()
                ->wherePivot('isowner', true)
                ->where('users.id', $user->id)
                ->exists();
        }

        return false;
    }

    public function accept(User $user, GroupJoinRequest $groupJoinRequest): bool
    {
        $group = Group::find($groupJoinRequest->groupid);
        if (!$group) {
            return false;
        }

        // Only group owners can accept requests
        return $group->members()
            ->wherePivot('isowner', true)
            ->where('users.id', $user->id)
            ->exists();
    }

    public function reject(User $user, GroupJoinRequest $groupJoinRequest): bool
    {
        return $this->accept($user, $groupJoinRequest);
    }


    public function cancel(User $user, GroupJoinRequest $groupJoinRequest): bool
    {
        $request = RequestModel::find($groupJoinRequest->requestid);
        
        // Only the sender can cancel their own request
        return $request && $request->senderid === $user->id;
    }
}
