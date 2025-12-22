<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class TagNotification extends Model
{
    protected $table = 'tag_notification';
    public $timestamps = false;
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationid',
        'postid',
        'taggerid',
    ];

    public function tagger()
    {
        return $this->belongsTo(\App\Models\User\User::class, 'taggerid', 'id');
    }

    public function getTaggerName()
    {
        $tagger = $this->tagger;
        if (!$tagger || $tagger->isdeleted) {
            return 'Deleted User';
        }
        return $tagger->name;
    }
}
