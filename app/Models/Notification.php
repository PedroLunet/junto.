<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\User\DeletedUser;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';

    public $timestamps = false;

    protected $fillable = [
        'message',
        'isread',
        'receiverid',
        'createdat',
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiverid');
    }

    public function getReceiverAttribute()
    {
        $user = $this->receiver;
        if (!$user || $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }

    public function getTypeAttribute()
    {
        if ($this->commentNotification) return 'comment';
        if ($this->tagNotification) return 'tag';
        if ($this->likeNotification) return 'like';
        if ($this->activityNotification) return 'activity';
        if ($this->friendRequest) return 'friend_request';
        if ($this->groupInviteRequest) return 'group_invite';
        if ($this->groupJoinRequest) return 'group_join';
        return 'general';
    }

    public function commentNotification()
    {
        return $this->hasOne(CommentNotification::class, 'notificationId', 'id');
    }

    public function tagNotification()
    {
        return $this->hasOne(TagNotification::class, 'notificationId', 'id');
    }

    public function likeNotification()
    {
        return $this->hasOne(LikeNotification::class, 'notificationId', 'id');
    }

    public function activityNotification()
    {
        return $this->hasOne(ActivityNotification::class, 'notificationId', 'id');
    }

    public function friendRequest()
    {
        return $this->hasOne(FriendRequest::class, 'notificationId', 'id');
    }

    public function groupInviteRequest()
    {
        return $this->hasOne(GroupInviteRequest::class, 'requestId', 'id');
    }

    public function groupJoinRequest()
    {
        return $this->hasOne(GroupJoinRequest::class, 'requestId', 'id');
    }
}
