<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
    protected $table = 'lbaw2544.friend_request';
    public $timestamps = false;
    protected $primaryKey = 'requestid';

    protected $fillable = [
        'requestid',
    ];
}
