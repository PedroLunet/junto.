<?php

namespace App\Models\Post;

use App\Models\User\User;
use App\Models\User\DeletedUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $table = 'post';

    public $timestamps = false;

    protected $fillable = [
        'userid',
        'groupid',
        'createdat',
    ];

    public function standardPost()
    {
        return $this->hasOne(StandardPost::class, 'postid', 'id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'postid', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'id');
    }

    public function group()
    {
        return $this->belongsTo(\App\Models\Group::class, 'groupid', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(User::class, 'post_tag', 'postid', 'userid')
            ->withPivot('createdat')
            ->orderBy('post_tag.createdat', 'asc');
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_like', 'postid', 'userid');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'postid', 'id');
    }

    public function getAuthorAttribute()
    {
        $user = $this->user;
        if ($user && $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }

    public static function getPostDetails($id, $currentUserId = null)
    {
        $sql = "
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilepicture as author_image,
                g.name as group_name,
                p.groupid as groupid,
                COALESCE(sp.text, r.content) as content,
                CASE 
                    WHEN sp.postid IS NOT NULL THEN 'standard'
                    WHEN r.postid IS NOT NULL THEN 'review'
                END as post_type,
                r.rating,
                m.title as media_title,
                m.coverimage as media_poster,
                m.releaseyear as media_year,
                m.creator as media_creator,
                CASE
                    WHEN EXISTS (SELECT 1 FROM book b WHERE b.mediaid = m.id) THEN 'book'
                    WHEN EXISTS (SELECT 1 FROM film f WHERE f.mediaid = m.id) THEN 'movie'
                    WHEN EXISTS (SELECT 1 FROM music mu WHERE mu.mediaid = m.id) THEN 'music'
                END as media_type,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postid = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM post_like pl WHERE pl.postid = p.id AND pl.userid = ?) as is_liked,' : 'FALSE as is_liked,') . "
                (SELECT COUNT(*) FROM comment c WHERE c.postid = p.id) as comments_count,
                sp.imageurl as image_url
            FROM post p
            JOIN users u ON p.userid = u.id
            LEFT JOIN groups g ON p.groupid = g.id
            LEFT JOIN standard_post sp ON p.id = sp.postid
            LEFT JOIN review r ON p.id = r.postid
            LEFT JOIN media m ON r.mediaid = m.id
            WHERE p.id = ? AND u.isdeleted = false AND u.isblocked = false
        ";

        $params = $currentUserId ? [$currentUserId, $id] : [$id];
        $post = DB::selectOne($sql, $params);

        if (!$post) {
            return null;
        }

        $posts = [$post];
        $posts = self::attachTagsToPostData($posts);

        return $posts[0];
    }

    public static function getPostsWithDetails($currentUserId = null)
    {
        $whereClause = "WHERE (p.groupId IS NOT NULL OR u.isPrivate = FALSE)";
        $params = [];

        if ($currentUserId) {
            $whereClause = "
                WHERE (
                    p.groupId IS NOT NULL
                    OR u.isPrivate = FALSE 
                    OR p.userId = ? 
                    OR EXISTS (
                        SELECT 1 FROM friendship f 
                        WHERE f.userId1 = LEAST(p.userId, ?) 
                        AND f.userId2 = GREATEST(p.userId, ?)
                    )
                )
            ";
            $params = [$currentUserId, $currentUserId, $currentUserId, $currentUserId];
        }

        $sql = "
            SELECT 
                p.id,
                p.groupId as groupid,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilePicture as author_image,
                g.name as group_name,
                COALESCE(sp.text, r.content) as content,
                CASE 
                    WHEN sp.postId IS NOT NULL THEN 'standard'
                    WHEN r.postId IS NOT NULL THEN 'review'
                END as post_type,
                r.rating,
                m.title as media_title,
                m.coverImage as media_poster,
                m.releaseYear as media_year,
                m.creator as media_creator,
                CASE
                    WHEN EXISTS (SELECT 1 FROM book b WHERE b.mediaId = m.id) THEN 'book'
                    WHEN EXISTS (SELECT 1 FROM film f WHERE f.mediaId = m.id) THEN 'movie'
                    WHEN EXISTS (SELECT 1 FROM music mu WHERE mu.mediaId = m.id) THEN 'music'
                END as media_type,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            LEFT JOIN standard_post sp ON p.id = sp.postId
            LEFT JOIN review r ON p.id = r.postId
            LEFT JOIN media m ON r.mediaId = m.id
            ' . $whereClause . '
            ORDER BY p.createdAt DESC
        ';

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function attachTagsToPostData($posts)
    {
        if (empty($posts)) {
            return $posts;
        }

        $postIds = array_map(function ($post) { return $post->id; }, $posts);
        $tagsSql = "
            SELECT pt.postId, u.id, u.name, u.username
            FROM post_tag pt
            JOIN users u ON pt.userId = u.id
            WHERE pt.postId IN (" . implode(',', array_fill(0, count($postIds), '?')) . ")
            AND u.isadmin = false
            ORDER BY pt.createdAt ASC
        ";
        
        $tags = DB::select($tagsSql, $postIds);
        
        $tagsByPost = [];
        foreach ($tags as $tag) {
            if (!isset($tagsByPost[$tag->postid])) {
                $tagsByPost[$tag->postid] = [];
            }
            $tagsByPost[$tag->postid][] = (object) [
                'id' => $tag->id,
                'name' => $tag->name,
                'username' => $tag->username,
            ];
        }

        foreach ($posts as $post) {
            $post->tagged_users = $tagsByPost[$post->id] ?? [];
        }

        return $posts;
    }

    public static function getFriendsPostsWithDetails($currentUserId)
    {
        $sql = "
            SELECT 
                p.id,
                p.groupId as groupid,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilePicture as author_image,
                g.name as group_name,
                COALESCE(sp.text, r.content) as content,
                CASE 
                    WHEN sp.postId IS NOT NULL THEN 'standard'
                    WHEN r.postId IS NOT NULL THEN 'review'
                END as post_type,
                r.rating,
                m.title as media_title,
                m.coverImage as media_poster,
                m.releaseYear as media_year,
                m.creator as media_creator,
                CASE
                    WHEN EXISTS (SELECT 1 FROM book b WHERE b.mediaId = m.id) THEN 'book'
                    WHEN EXISTS (SELECT 1 FROM film f WHERE f.mediaId = m.id) THEN 'movie'
                    WHEN EXISTS (SELECT 1 FROM music mu WHERE mu.mediaId = m.id) THEN 'music'
                END as media_type,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) > 0 FROM post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            LEFT JOIN standard_post sp ON p.id = sp.postId
            LEFT JOIN review r ON p.id = r.postId
            LEFT JOIN media m ON r.mediaId = m.id
            WHERE p.userId IN (
                SELECT userId2 FROM friendship WHERE userId1 = ?
                UNION
                SELECT userId1 FROM friendship WHERE userId2 = ?
            )
            ORDER BY p.createdAt DESC
        ";

        $posts = DB::select($sql, [$currentUserId, $currentUserId, $currentUserId]);
        
        return self::attachTagsToPostData($posts);
    }

    public static function getMovieReviewPosts($currentUserId = null)
    {
        $whereClause = "WHERE EXISTS (SELECT 1 FROM film f WHERE f.mediaId = m.id) AND p.groupId IS NULL AND u.isPrivate = FALSE";
        $params = [];

        if ($currentUserId) {
            $whereClause = "
                WHERE EXISTS (SELECT 1 FROM film f WHERE f.mediaId = m.id) 
                AND p.groupId IS NULL
                AND (
                    u.isPrivate = FALSE 
                    OR p.userId = ? 
                    OR EXISTS (
                        SELECT 1 FROM friendship f 
                        WHERE f.userId1 = LEAST(p.userId, ?) 
                        AND f.userId2 = GREATEST(p.userId, ?)
                    )
                )
            ";
            $params = [$currentUserId, $currentUserId, $currentUserId, $currentUserId];
        }

        $sql = "
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilePicture as author_image,
                g.name as group_name,
                r.content as content,
                'review' as post_type,
                r.rating,
                m.title as media_title,
                m.coverImage as media_poster,
                m.releaseYear as media_year,
                m.creator as media_creator,
                'movie' as media_type,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count,
                NULL as image_url
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            JOIN review r ON p.id = r.postId
            JOIN media m ON r.mediaId = m.id
            ' . $whereClause . '
            ORDER BY p.createdAt DESC
        ';

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function getBookReviewPosts($currentUserId = null)
    {
        $whereClause = "WHERE EXISTS (SELECT 1 FROM book b WHERE b.mediaId = m.id) AND p.groupId IS NULL AND u.isPrivate = FALSE";
        $params = [];

        if ($currentUserId) {
            $whereClause = "
                WHERE EXISTS (SELECT 1 FROM book b WHERE b.mediaId = m.id) 
                AND p.groupId IS NULL
                AND (
                    u.isPrivate = FALSE 
                    OR p.userId = ? 
                    OR EXISTS (
                        SELECT 1 FROM friendship f 
                        WHERE f.userId1 = LEAST(p.userId, ?) 
                        AND f.userId2 = GREATEST(p.userId, ?)
                    )
                )
            ";
            $params = [$currentUserId, $currentUserId, $currentUserId, $currentUserId];
        }

        $sql = "
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilePicture as author_image,
                g.name as group_name,
                r.content as content,
                'review' as post_type,
                r.rating,
                m.title as media_title,
                m.coverImage as media_poster,
                m.releaseYear as media_year,
                m.creator as media_creator,
                'book' as media_type,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count,
                NULL as image_url
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            JOIN review r ON p.id = r.postId
            JOIN media m ON r.mediaId = m.id
            ' . $whereClause . '
            ORDER BY p.createdAt DESC
        ';

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function getMusicReviewPosts($currentUserId = null)
    {
        $whereClause = "WHERE EXISTS (SELECT 1 FROM music mu WHERE mu.mediaId = m.id) AND p.groupId IS NULL AND u.isPrivate = FALSE";
        $params = [];

        if ($currentUserId) {
            $whereClause = "
                WHERE EXISTS (SELECT 1 FROM music mu WHERE mu.mediaId = m.id) 
                AND p.groupId IS NULL
                AND (
                    u.isPrivate = FALSE 
                    OR p.userId = ? 
                    OR EXISTS (
                        SELECT 1 FROM friendship f 
                        WHERE f.userId1 = LEAST(p.userId, ?) 
                        AND f.userId2 = GREATEST(p.userId, ?)
                    )
                )
            ";
            $params = [$currentUserId, $currentUserId, $currentUserId, $currentUserId];
        }

        $sql = "
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilePicture as author_image,
                g.name as group_name,
                r.content as content,
                'review' as post_type,
                r.rating,
                m.title as media_title,
                m.coverImage as media_poster,
                m.releaseYear as media_year,
                m.creator as media_creator,
                'music' as media_type,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count,
                NULL as image_url
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            JOIN review r ON p.id = r.postId
            JOIN media m ON r.mediaId = m.id
            ' . $whereClause . '
            ORDER BY p.createdAt DESC
        ';

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function toggleLike($postId, $userId)
    {
        $post = self::find($postId);
        $user = User::find($userId);
        
        $existing = DB::table('post_like')
            ->where('postid', $postId)
            ->where('userid', $userId)
            ->first();

        if ($existing) {
            DB::table('post_like')
                ->where('postid', $postId)
                ->where('userid', $userId)
                ->delete();
            $liked = false;
        } else {
            DB::table('post_like')->insert([
                'postid' => $postId,
                'userid' => $userId,
                'createdat' => now(),
            ]);
            $liked = true;
        }

        $likesCount = DB::table('post_like')
            ->where('postid', $postId)
            ->count();

        return [
            'liked' => $liked,
            'likes_count' => $likesCount,
        ];
    }

    public static function getUserStandardPosts($userId)
    {
        return DB::select('
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                g.name as group_name,
                sp.text as content,
                sp.imageUrl as image_url,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            JOIN standard_post sp ON p.id = sp.postId
            WHERE p.userId = ?
            ORDER BY p.createdAt DESC
        ', [$userId]);
    }

    public static function getUserReviewPosts($userId)
    {
        return DB::select('
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                g.name as group_name,
                r.content,
                r.rating,
                m.title as media_title,
                m.coverimage as image_url,
                (SELECT COUNT(*) FROM post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) FROM comment c WHERE c.postId = p.id) as comments_count
            FROM post p
            JOIN users u ON p.userId = u.id
            LEFT JOIN groups g ON p.groupId = g.id
            JOIN review r ON p.id = r.postId
            JOIN media m ON r.mediaId = m.id
            WHERE p.userId = ?
            ORDER BY p.createdAt DESC
        ', [$userId]);
    }
}
