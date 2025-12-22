<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $isprivate
 * @property string|null $icon
 */
class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'isprivate',
        'icon',
    ];

    protected $casts = [
        'isprivate' => 'boolean',
    ];

    public function members()
    {
        return $this->belongsToMany(\App\Models\User\User::class, 'membership', 'groupid', 'userid')
            ->where('users.isadmin', false)
            ->withPivot('isowner');
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
