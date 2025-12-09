<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $table = 'membership';

    protected $primaryKey = ['userId', 'groupId'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'userId',
        'groupId',
        'isOwner',
    ];
}
