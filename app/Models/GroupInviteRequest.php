<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupInviteRequest extends Model
{
    protected $table = 'lbaw2544.group_invite_request';
    public $timestamps = false;
    protected $primaryKey = 'requestid';

    protected $fillable = [
        'requestid',
        'groupid',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'groupid');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'requestid', 'notificationid');
    }
}
