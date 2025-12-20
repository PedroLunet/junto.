<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class TagNotification extends Model
{
    protected $table = 'lbaw2544.tag_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'postid',
    ];
}
