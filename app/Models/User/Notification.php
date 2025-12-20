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

    /**
     * Get the user who receives this notification.
     */
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

    /**
     * Get the request associated with this notification (if any).
     */
    public function request()
    {
        return $this->hasOne(Request::class, 'notificationid', 'id');
    }

    /**
     * Get the activity notification associated with this notification.
     */
    public function activityNotification()
    {
        return $this->hasOne(ActivityNotification::class, 'notificationid', 'id');
    }

    /**
     * Mark this notification as read.
     */
    public function markAsRead()
    {
        if (!$this->isread) {
            $this->isread = true;
            $this->save();
        }
    }

    /**
     * Mark this notification as unread.
     */
    public function markAsUnread()
    {
        if ($this->isread) {
            $this->isread = false;
            $this->save();
        }
    }
}
