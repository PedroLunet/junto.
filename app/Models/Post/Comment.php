<?php

namespace App\Models\Post;

use App\Models\User\User;
use App\Models\User\DeletedUser;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comment';
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

    public function likes()
    {
        return $this->belongsToMany(User::class, 'comment_like', 'commentid', 'userid');
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
        $currentUserId = auth()->id();

        $comments = self::where('postid', $postId)
            ->with('user')
            ->withCount(['likes as likes_count'])
            ->orderBy('createdat', 'asc')
            ->get();

        if ($currentUserId) {
            foreach ($comments as $comment) {
                $comment->is_liked = \Illuminate\Support\Facades\DB::table('comment_like')
                    ->where('commentid', $comment->id)
                    ->where('userid', $currentUserId)
                    ->exists();
                $comment->user_id = $comment->userid;
            }
        } else {
            foreach ($comments as $comment) {
                $comment->is_liked = false;
                $comment->user_id = $comment->userid;
            }
        }

        return $comments;
    }

    public function getAuthorNameAttribute()
    {
        $author = $this->author;
        return $author ? $author->name : 'Unknown';
    }

    public function getUsernameAttribute()
    {
        $author = $this->author;
        return $author ? $author->username : 'unknown';
    }

    public function getPostAuthorNameAttribute()
    {
        // Try to get post author safely
        if ($this->post && $this->post->user) {
            return $this->post->user->name;
        }
        return 'Unknown';
    }

    public function getAuthorPictureAttribute()
    {
        $author = $this->author;
        return $author ? $author->profilepicture : null;
    }

    public static function addComment($postId, $userId, $content)
    {
        $comment = self::create([
            'postid' => $postId,
            'userid' => $userId,
            'content' => $content,
            'createdat' => now(),
        ]);

        // Return the fresh comment with relations and counts to match expected output
        $commentModel = self::where('id', $comment->id)
            ->with('user')
            ->withCount(['likes as likes_count'])
            ->first();

        if ($commentModel) {
            $commentModel->author_name = $commentModel->user->name;
            $commentModel->username = $commentModel->user->username;
            $commentModel->author_picture = $commentModel->user->profilepicture;
        }

        return $commentModel;


    }

    public static function toggleLike($commentId, $userId)
    {
        $comment = self::find($commentId);
        $user = User::find($userId);

        $existing = \Illuminate\Support\Facades\DB::table('comment_like')
            ->where('commentid', $commentId)
            ->where('userid', $userId)
            ->first();

        if ($existing) {
            \Illuminate\Support\Facades\DB::table('comment_like')
                ->where('commentid', $commentId)
                ->where('userid', $userId)
                ->delete();
            $liked = false;
        } else {
            \Illuminate\Support\Facades\DB::table('comment_like')->insert([
                'commentid' => $commentId,
                'userid' => $userId,
                'createdat' => now(),
            ]);
            $liked = true;
        }

        $likesCount = \Illuminate\Support\Facades\DB::table('comment_like')
            ->where('commentid', $commentId)
            ->count();

        return [
            'liked' => $liked,
            'likes_count' => $likesCount,
        ];
    }
}
