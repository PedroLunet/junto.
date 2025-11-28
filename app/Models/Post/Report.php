<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Report extends Model
{
  protected $table = 'lbaw2544.report';
  public $timestamps = false;

  /**
   * Create a new report for a post
   */
  public static function createPostReport($postId, $reason)
  {
    DB::statement("SELECT lbaw2544.fn_report_post(?, ?)", [$postId, $reason]);

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
            FROM lbaw2544.report r
            LEFT JOIN lbaw2544.post p ON r.postId = p.id
            LEFT JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            LEFT JOIN lbaw2544.review rev ON p.id = rev.postId
            LEFT JOIN lbaw2544.comment c ON r.commentId = c.id
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
            FROM lbaw2544.report r
            LEFT JOIN lbaw2544.post p ON r.postId = p.id
            LEFT JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            LEFT JOIN lbaw2544.review rev ON p.id = rev.postId
            LEFT JOIN lbaw2544.comment c ON r.commentId = c.id
            WHERE r.status = 'pending'
            ORDER BY r.createdAt DESC
        ");
  }

  /**
   * Update report status (accept or reject)
   */
  public static function updateReportStatus($reportId, $newStatus)
  {
    DB::statement("SELECT lbaw2544.fn_manage_report(?, ?)", [$reportId, $newStatus]);

    return true;
  }
}
