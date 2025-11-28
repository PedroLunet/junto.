<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
  public $timestamps = false;

  protected $table = 'request';
  protected $primaryKey = 'notificationid';

  protected $fillable = [
    'notificationid',
    'status',
    'senderid',
  ];

  protected $casts = [
    'status' => 'string',
  ];

  /**
   * Get the notification associated with this request.
   */
  public function notification()
  {
    return $this->belongsTo(Notification::class, 'notificationid', 'id');
  }

  /**
   * Get the sender of this request.
   */
  public function sender()
  {
    return $this->belongsTo(User::class, 'senderid', 'id');
  }

  /**
   * Get the receiver of this request through the notification.
   */
  public function receiver()
  {
    return $this->hasOneThrough(
      User::class,
      Notification::class,
      'id',           // Foreign key on notifications table
      'id',           // Foreign key on users table
      'notificationid', // Local key on requests table
      'receiverid'    // Local key on notifications table
    );
  }

  /**
   * Get the friend request associated with this request (if any).
   */
  public function friendRequest()
  {
    return $this->hasOne(FriendRequest::class, 'requestid', 'notificationid');
  }

  /**
   * Check if the request is pending.
   */
  public function isPending(): bool
  {
    return $this->status === 'pending';
  }

  /**
   * Check if the request is accepted.
   */
  public function isAccepted(): bool
  {
    return $this->status === 'accepted';
  }

  /**
   * Check if the request is rejected.
   */
  public function isRejected(): bool
  {
    return $this->status === 'rejected';
  }
}
