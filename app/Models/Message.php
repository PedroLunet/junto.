<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\User\DeletedUser;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false; 
    protected $table = 'messages';

    protected $fillable = [
        'senderid',
        'receiverid',
        'content',
        'isread',
        'sentat',
    ];

    protected $casts = [
        'sentat' => 'datetime',
        'isread' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'senderid');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiverid');
    }

    public function getSenderAttribute()
    {
        $user = $this->sender;
        if (!$user || $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }

    public function getReceiverAttribute()
    {
        $user = $this->receiver;
        if (!$user || $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }
}