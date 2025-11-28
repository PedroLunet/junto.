<?php

namespace App\Models\Post;

use App\Models\User\User;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'lbaw2544.comment';
    public $timestamps = false;

    protected $fillable = [
        'postid',
        'userid',
        'content',
        'createdat'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'postid', 'id');
    }

    public static function getCommentsForPost($postId)
    {
        return \Illuminate\Support\Facades\DB::select("
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
        \Illuminate\Support\Facades\DB::insert("
            INSERT INTO lbaw2544.comment (postId, userId, content, createdAt)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ", [$postId, $userId, $content]);

        // Get the newly created comment
        $comments = \Illuminate\Support\Facades\DB::select("
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
}
