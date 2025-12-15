<?php

namespace App\Models\Notification;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Model;

class LikeNotification extends Model
{
    protected $table = 'lbaw2544.like_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'postid',
    ];

    /**
     * Get the activity notification associated with this like notification.
     */
    public function activityNotification()
    {
        return $this->hasOne(ActivityNotification::class, 'notificationid', 'notificationid');
    }

    /**
     * Get the post that was liked.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'postid', 'id');
    }
}
