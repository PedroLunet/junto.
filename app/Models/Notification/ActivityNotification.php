<?php

namespace App\Models\Notification;

use App\Models\User\Notification;
use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Model;

class ActivityNotification extends Model
{
    protected $table = 'lbaw2544.activity_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'postid',
    ];

    /**
     * Get the notification associated with this activity notification.
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notificationid', 'id');
    }

    /**
     * Get the post associated with this activity notification.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'postid', 'id');
    }
}
