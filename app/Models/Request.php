<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = 'request';

    protected $primaryKey = 'notificationid';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'notificationid',
        'status',
        'senderid',
    ];

    public function groupJoinRequest()
    {
        return $this->hasOne(GroupJoinRequest::class, 'requestid', 'notificationid');
    }
}
