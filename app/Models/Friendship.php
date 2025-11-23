<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
  public $timestamps = false;

  protected $table = 'friendship';

  // Composite primary key
  protected $primaryKey = null;
  public $incrementing = false;

  protected $fillable = [
    'userid1',
    'userid2',
    'createdat',
  ];

  protected $casts = [
    'createdat' => 'datetime',
  ];

  /**
   * Get the first user in the friendship.
   */
  public function user1()
  {
    return $this->belongsTo(User::class, 'userid1', 'id');
  }

  /**
   * Get the second user in the friendship.
   */
  public function user2()
  {
    return $this->belongsTo(User::class, 'userid2', 'id');
  }

  /**
   * Check if a friendship exists between two users.
   */
  public static function exists(int $userId1, int $userId2): bool
  {
    $minId = min($userId1, $userId2);
    $maxId = max($userId1, $userId2);

    return self::where('userid1', $minId)
      ->where('userid2', $maxId)
      ->exists();
  }

  /**
   * Delete a friendship between two users.
   */
  public static function unfriend(int $userId1, int $userId2): bool
  {
    $minId = min($userId1, $userId2);
    $maxId = max($userId1, $userId2);

    return self::where('userid1', $minId)
      ->where('userid2', $maxId)
      ->delete() > 0;
  }
}
