<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'isPrivate',
        'icon',
    ];

    public function members()
    {
        return $this->belongsToMany(\App\Models\User\User::class, 'membership', 'groupid', 'userid')->withPivot('isowner');
    }

    public function owner()
    {
        return $this->members()->wherePivot('isowner', true);
    }

    // Relationship for group posts
    public function posts()
    {
        return $this->hasMany(\App\Models\Post\Post::class, 'groupid');
    }
}
