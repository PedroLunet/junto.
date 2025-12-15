<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupJoinRequest extends Model
{
    protected $table = 'group_join_request';

    protected $primaryKey = 'requestid';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'requestid',
        'groupid',
    ];
}
