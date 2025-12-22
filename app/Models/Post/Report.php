<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Report extends Model
{
  protected $table = 'report';
  public $timestamps = false;

  protected $fillable = [
    'reason',
    'status',
    'createdat',
    'postid',
    'commentid'
  ];

  public function post()
  {
    return $this->belongsTo(Post::class, 'postid', 'id');
  }

  public function comment()
  {
    return $this->belongsTo(Comment::class, 'commentid', 'id');
  }
  public function getPostIdAttribute()
  {
    return $this->attributes['postid'] ?? null;
  }

  public function getCommentIdAttribute()
  {
    return $this->attributes['commentid'] ?? null;
  }

  /**
   * Create a new report for a post
   */
  public static function createPostReport($postId, $reason)
  {
    DB::statement("SELECT fn_report_post(?, ?)", [$postId, $reason]);

    return true;
  }

  /**
   * Get all reports (for admin view)
   */
  public static function getAllReports()
  {
    return DB::select("
            SELECT 
                r.id,
                r.reason,
                r.status,
                r.createdAt as created_at,
                r.postId as post_id,
                r.commentId as comment_id,
                CASE 
                    WHEN r.postId IS NOT NULL THEN 'post'
                    WHEN r.commentId IS NOT NULL THEN 'comment'
                END as report_type,
                u.name as post_author,
                u.username as post_author_username,
                COALESCE(sp.text, rev.content, c.content) as content
            FROM report r
            LEFT JOIN post p ON r.postId = p.id
            LEFT JOIN users u ON p.userId = u.id
            LEFT JOIN standard_post sp ON p.id = sp.postId
            LEFT JOIN review rev ON p.id = rev.postId
            LEFT JOIN comment c ON r.commentId = c.id
            ORDER BY r.createdAt DESC
        ");
  }

  /**
   * Get pending reports (for admin view)
   */
  public static function getPendingReports()
  {
    return DB::select("
            SELECT 
                r.id,
                r.reason,
                r.status,
                r.createdAt as created_at,
                r.postId as post_id,
                r.commentId as comment_id,
                CASE 
                    WHEN r.postId IS NOT NULL THEN 'post'
                    WHEN r.commentId IS NOT NULL THEN 'comment'
                END as report_type,
                u.name as post_author,
                u.username as post_author_username,
                COALESCE(sp.text, rev.content, c.content) as content
            FROM report r
            LEFT JOIN post p ON r.postId = p.id
            LEFT JOIN users u ON p.userId = u.id
            LEFT JOIN standard_post sp ON p.id = sp.postId
            LEFT JOIN review rev ON p.id = rev.postId
            LEFT JOIN comment c ON r.commentId = c.id
            WHERE r.status = 'pending'
            ORDER BY r.createdAt DESC
        ");
  }

  /**
   * Update report status (accept or reject)
   */
  public static function updateReportStatus($reportId, $newStatus)
  {
    DB::statement("SELECT fn_manage_report(?, ?)", [$reportId, $newStatus]);

    return true;
  }
}
