<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Import Eloquent relationship classes.

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Disable default created_at and updated_at timestamps for this model.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * Only these fields may be filled using methods like create() or update().
     * This protects against mass-assignment vulnerabilities.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'passwordhash',
    ];

    /**
     * The attributes that should be hidden when serializing the model
     * (e.g., to arrays or JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'passwordhash',
        'remember_token',
    ];

    protected $casts = [
        'isprivate' => 'boolean',
        'isadmin' => 'boolean',
        'isblocked' => 'boolean',
    ];

    /**
     * The attributes that should be cast to a specific type.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->passwordhash;
    }

    /**
     * Get the name of the password column.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return 'passwordhash';
    }

    public function favoriteFilmMedia()
    {
        return $this->belongsTo(Media::class, 'favoritefilm', 'id');
    }

    public function favoriteBookMedia()
    {
        return $this->belongsTo(Media::class, 'favoritebook', 'id');
    }

    public function favoriteSongMedia()
    {
        return $this->belongsTo(Media::class, 'favoritesong', 'id');
    }
}
