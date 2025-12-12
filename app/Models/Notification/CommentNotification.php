<?php

namespace App\Models\Notification;

use App\Models\Post\Comment;
use Illuminate\Database\Eloquent\Model;

class CommentNotification extends Model
{
    protected $table = 'lbaw2544.comment_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'commentid',
    ];

    /**
     * Get the activity notification associated with this comment notification.
     */
    public function activityNotification()
    {
        return $this->hasOne(ActivityNotification::class, 'notificationid', 'notificationid');
    }

    /**
     * Get the comment that triggered the notification.
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'commentid', 'id');
    }
}
