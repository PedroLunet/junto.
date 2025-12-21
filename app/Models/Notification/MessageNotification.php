<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class MessageNotification extends Model
{
    protected $table = 'lbaw2544.message_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'messageid',
    ];

    public function notification()
    {
        return $this->belongsTo(\App\Models\User\Notification::class, 'notificationid', 'id');
    }

    public function message()
    {
        return $this->belongsTo(\App\Models\Message::class, 'messageid', 'id');
    }
}
