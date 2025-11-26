<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FriendRequest extends Model
{
  public $timestamps = false;

  protected $table = 'friend_request';
  protected $primaryKey = 'requestid';

  protected $fillable = [
    'requestid',
  ];

  /**
   * Get the parent request.
   */
  public function request()
  {
    return $this->belongsTo(Request::class, 'requestid', 'notificationid');
  }

  /**
   * Get the sender of the friend request.
   */
  public function sender()
  {
    return $this->hasOneThrough(
      User::class,
      Request::class,
      'notificationid', // Foreign key on requests table
      'id',             // Foreign key on users table
      'requestid',      // Local key on friend_requests table
      'senderid'        // Local key on requests table
    );
  }

  /**
   * Get the receiver of the friend request.
   */
  public function receiver()
  {
    return $this->hasOneThrough(
      User::class,
      Request::class,
      'notificationid',  // Foreign key on requests table
      'id',              // Foreign key on users table
      'requestid',       // Local key on friend_requests table
      'receiverid'       // Note: This goes through notification
    );
  }

  /**
   * Get the notification associated with this friend request.
   */
  public function notification()
  {
    return $this->hasOneThrough(
      Notification::class,
      Request::class,
      'notificationid', // Foreign key on requests table
      'id',             // Foreign key on notifications table
      'requestid',      // Local key on friend_requests table
      'notificationid'  // Local key on requests table
    );
  }

  /**
   * Accept the friend request and create a friendship.
   */
  public function accept()
  {
    DB::transaction(function () {
      // Get the request
      $request = $this->request;

      // Update request status to accepted
      $request->status = 'accepted';
      $request->save();

      // Mark notification as read
      $notification = $request->notification;
      $notification->isread = true;
      $notification->save();

      // Get sender and receiver
      $senderId = $request->senderid;
      $receiverId = $notification->receiverid;

      // Create friendship (ensure smaller ID is first)
      $userId1 = min($senderId, $receiverId);
      $userId2 = max($senderId, $receiverId);

      // Use firstOrCreate - database will handle createdat with DEFAULT CURRENT_TIMESTAMP
      Friendship::firstOrCreate([
        'userid1' => $userId1,
        'userid2' => $userId2,
      ]);
    });
  }

  /**
   * Reject the friend request.
   */
  public function reject()
  {
    DB::transaction(function () {
      // Get the request
      $request = $this->request;

      // Update request status to rejected
      $request->status = 'rejected';
      $request->save();

      // Mark notification as read
      $notification = $request->notification;
      $notification->isread = true;
      $notification->save();
    });
  }

  /**
   * Check if the request is pending.
   */
  public function isPending(): bool
  {
    return $this->request->status === 'pending';
  }

  /**
   * Static method to send a friend request.
   */
  public static function send(int $senderId, int $receiverId)
  {
    return DB::transaction(function () use ($senderId, $receiverId) {
      // Get sender name for notification message
      $sender = User::findOrFail($senderId);

      // Create notification
      $notification = Notification::create([
        'message' => "You received a friend request from {$sender->name}",
        'isread' => false,
        'receiverid' => $receiverId,
        'createdat' => now(),
      ]);

      // Create request
      $request = Request::create([
        'notificationid' => $notification->id,
        'status' => 'pending',
        'senderid' => $senderId,
      ]);

      // Create friend request
      $friendRequest = self::create([
        'requestid' => $notification->id,
      ]);

      return $friendRequest;
    });
  }
}
