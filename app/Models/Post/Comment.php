<?php

namespace App\Models\Post;

use App\Models\User\User;
use App\Models\User\DeletedUser;
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

    public function getAuthorAttribute()
    {
        $user = $this->user;
        if ($user && $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
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
                u.profilePicture as author_picture,
                (SELECT COUNT(*) FROM comment_like cl WHERE cl.commentId = c.id) as likes_count
            FROM comment c
            JOIN users u ON c.userId = u.id
            WHERE c.postId = ?
            ORDER BY c.createdAt ASC
        ", [$postId]);
    }

    public static function addComment($postId, $userId, $content)
    {
        $comment = self::create([
            'postid' => $postId,
            'userid' => $userId,
            'content' => $content,
            'createdat' => now(),
        ]);

        // Get the newly created comment details
        $comments = \Illuminate\Support\Facades\DB::select("
            SELECT 
                c.id,
                c.content,
                c.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilePicture as author_picture,
                (SELECT COUNT(*) FROM comment_like cl WHERE cl.commentId = c.id) as likes_count
            FROM comment c
            JOIN users u ON c.userId = u.id
            WHERE c.id = ?
        ", [$comment->id]);

        return $comments[0] ?? null;
    }
}
