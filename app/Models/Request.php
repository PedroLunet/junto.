<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\User\DeletedUser;
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

    public function sender()
    {
        return $this->belongsTo(User::class, 'senderid');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notificationid');
    }

    public function getSenderAttribute()
    {
        $user = $this->sender;
        if (!$user || $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }

    public function groupJoinRequest()
    {
        return $this->hasOne(GroupJoinRequest::class, 'requestid', 'notificationid');
    }
}
