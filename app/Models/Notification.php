<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  public $timestamps = false;

  protected $table = 'notification';

  protected $fillable = [
    'message',
    'isread',
    'receiverid',
    'createdat',
  ];

  protected $casts = [
    'isread' => 'boolean',
    'createdat' => 'datetime',
  ];

  /**
   * Get the user who receives this notification.
   */
  public function receiver()
  {
    return $this->belongsTo(User::class, 'receiverid', 'id');
  }

  /**
   * Get the request associated with this notification (if any).
   */
  public function request()
  {
    return $this->hasOne(Request::class, 'notificationid', 'id');
  }

  /**
   * Mark this notification as read.
   */
  public function markAsRead()
  {
    if (!$this->isread) {
      $this->isread = true;
      $this->save();
    }
  }

  /**
   * Mark this notification as unread.
   */
  public function markAsUnread()
  {
    if ($this->isread) {
      $this->isread = false;
      $this->save();
    }
  }
}
