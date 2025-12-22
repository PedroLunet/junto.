<?php

namespace App\Models\Notification;

use App\Models\User\Notification;
use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Model;

class ActivityNotification extends Model
{
    protected $table = 'activity_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'postid',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notificationid', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'postid', 'id');
    }

    public function likeNotification()
    {
        return $this->hasOne(LikeNotification::class, 'notificationid', 'notificationid');
    }

    public function commentNotification()
    {
        return $this->hasOne(CommentNotification::class, 'notificationid', 'notificationid');
    }
}
