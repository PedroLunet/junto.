<?php

namespace App\Models\User;

use App\Models\User\User;
use App\Models\User\DeletedUser;
use App\Models\Notification\ActivityNotification;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $table = 'lbaw2544.notification';

    protected $fillable = [
        'message',
        'isread',
        'receiverid',
        'createdat',
    ];

    protected $casts = [
        'isread' => 'boolean',
        'createdat' => 'datetime',
    ];

  
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiverid', 'id');
    }

    public function getReceiverAttribute()
    {
        $user = $this->receiver;
        if (!$user || $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }

    public function request()
    {
        return $this->hasOne(Request::class, 'notificationid', 'id');
    }

 
    public function activityNotification()
    {
        return $this->hasOne(ActivityNotification::class, 'notificationid', 'id');
    }

    public function groupInviteRequest()
    {
        return $this->hasOne(\App\Models\GroupInviteRequest::class, 'requestid', 'id');
    }

    public function groupJoinRequest()
    {
        return $this->hasOne(\App\Models\GroupJoinRequest::class, 'requestid', 'id');
    }

    public function getTypeAttribute()
    {
        if ($this->activityNotification) return 'activity';
        if ($this->groupInviteRequest) return 'group_invite';
        if ($this->groupJoinRequest) return 'group_join';
        return null;
    }
 
    public function markAsRead()
    {
        if (!$this->isread) {
            $this->isread = true;
            $this->save();
        }
    }

   
    public function markAsUnread()
    {
        if ($this->isread) {
            $this->isread = false;
            $this->save();
        }
    }

  
    public function scopeExcludeSelfInteractions($query)
    {
        return $query
            ->whereNotIn('id', function ($subquery) {
                $subquery->select('an.notificationid')
                    ->from('activity_notification as an')
                    ->join('like_notification as ln', 'an.notificationid', '=', 'ln.notificationid')
                    ->join('post_like as pl', 'ln.postid', '=', 'pl.postid')
                    ->join('post as p', 'ln.postid', '=', 'p.id')
                    ->whereColumn('pl.userid', '=', 'p.userid');
            })
            ->whereNotIn('id', function ($subquery) {
                $subquery->select('an.notificationid')
                    ->from('activity_notification as an')
                    ->join('comment_notification as cn', 'an.notificationid', '=', 'cn.notificationid')
                    ->join('comment as c', 'cn.commentid', '=', 'c.id')
                    ->join('post as p', 'c.postid', '=', 'p.id')
                    ->whereColumn('c.userid', '=', 'p.userid');
            });
    }
}
