<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $table = 'lbaw2544.post';
    public $timestamps = false;

    public static function getPostsWithDetails()
    {
        return DB::select("
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                COALESCE(sp.text, r.content) as content,
                CASE 
                    WHEN sp.postId IS NOT NULL THEN 'standard'
                    WHEN r.postId IS NOT NULL THEN 'review'
                END as post_type,
                r.rating,
                m.title as media_title,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            LEFT JOIN lbaw2544.review r ON p.id = r.postId
            LEFT JOIN lbaw2544.media m ON r.mediaId = m.id
            ORDER BY p.id DESC
        ");
    }

    public static function getCommentsForPost($postId)
    {
        return DB::select("
            SELECT 
                c.id,
                c.content,
                c.createdAt as created_at,
                u.name as author_name,
                u.username,
                (SELECT COUNT(*) FROM lbaw2544.comment_like cl WHERE cl.commentId = c.id) as likes_count
            FROM lbaw2544.comment c
            JOIN lbaw2544.users u ON c.userId = u.id
            WHERE c.postId = ?
            ORDER BY c.createdAt ASC
        ", [$postId]);
    }

    public static function addComment($postId, $userId, $content)
    {
        DB::insert("
            INSERT INTO lbaw2544.comment (postId, userId, content, createdAt)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ", [$postId, $userId, $content]);

        // Get the newly created comment
        $comments = DB::select("
            SELECT 
                c.id,
                c.content,
                c.createdAt as created_at,
                u.name as author_name,
                u.username,
                (SELECT COUNT(*) FROM lbaw2544.comment_like cl WHERE cl.commentId = c.id) as likes_count
            FROM lbaw2544.comment c
            JOIN lbaw2544.users u ON c.userId = u.id
            WHERE c.postId = ? AND c.userId = ?
            ORDER BY c.createdAt DESC
            LIMIT 1
        ", [$postId, $userId]);

        return $comments[0] ?? null;
    }

    public static function toggleLike($postId, $userId)
    {
        // Check if already liked
        $existing = DB::select("
            SELECT * FROM lbaw2544.post_like
            WHERE postId = ? AND userId = ?
        ", [$postId, $userId]);

        if (count($existing) > 0) {
            // Unlike
            DB::delete("
                DELETE FROM lbaw2544.post_like
                WHERE postId = ? AND userId = ?
            ", [$postId, $userId]);
            $liked = false;
        } else {
            // Like
            DB::insert("
                INSERT INTO lbaw2544.post_like (postId, userId, createdAt)
                VALUES (?, ?, CURRENT_TIMESTAMP)
            ", [$postId, $userId]);
            $liked = true;
        }

        // Get updated likes count
        $count = DB::select("
            SELECT COUNT(*) as count FROM lbaw2544.post_like
            WHERE postId = ?
        ", [$postId]);

        return [
            'liked' => $liked,
            'likes_count' => $count[0]->count
        ];
    }
}