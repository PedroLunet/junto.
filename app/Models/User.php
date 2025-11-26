<?php

namespace App\Models;

use App\Models\Friendship;
use App\Models\FriendRequest;
use App\Models\Notification;
use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Import Eloquent relationship classes.

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Disable default created_at and updated_at timestamps for this model.
    public $timestamps = false;

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
        'username',
        'bio',
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

    /**
     * Get all friends of this user (both directions).
     */
    public function friends()
    {
        // Friends where this user is user1
        $friends1 = $this->belongsToMany(
            User::class,
            'friendship',
            'userid1',
            'userid2'
        );

        // Friends where this user is user2
        $friends2 = $this->belongsToMany(
            User::class,
            'friendship',
            'userid2',
            'userid1'
        );

        // Merge both collections
        return $friends1->union($friends2->getQuery());
    }

    /**
     * Get friend requests sent by this user.
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(Request::class, 'senderid', 'id')
            ->whereHas('friendRequest');
    }

    /**
     * Get friend requests received by this user.
     */
    public function receivedFriendRequests()
    {
        return $this->hasManyThrough(
            FriendRequest::class,
            Notification::class,
            'receiverid',    // Foreign key on notifications table
            'requestid',     // Foreign key on friend_requests table
            'id',            // Local key on users table
            'id'             // Local key on notifications table
        )->whereHas('request', function ($query) {
            $query->where('status', 'pending');
        });
    }

    /**
     * Get all notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'receiverid', 'id')
            ->orderBy('createdat', 'desc');
    }

    /**
     * Check if this user is friends with another user.
     */
    public function isFriendsWith(int $userId): bool
    {
        return Friendship::exists($this->id, $userId);
    }

    /**
     * Check if this user has sent a friend request to another user.
     */
    public function hasSentFriendRequestTo(int $userId): bool
    {
        return Request::where('senderid', $this->id)
            ->whereHas('notification', function ($query) use ($userId) {
                $query->where('receiverid', $userId);
            })
            ->where('status', 'pending')
            ->whereHas('friendRequest')
            ->exists();
    }

    /**
     * Check if this user has received a friend request from another user.
     */
    public function hasReceivedFriendRequestFrom(int $userId): bool
    {
        return Request::where('senderid', $userId)
            ->whereHas('notification', function ($query) {
                $query->where('receiverid', $this->id);
            })
            ->where('status', 'pending')
            ->whereHas('friendRequest')
            ->exists();
    }
}
