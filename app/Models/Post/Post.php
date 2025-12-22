<?php

namespace App\Models\Post;

use App\Models\User\User;
use App\Models\User\DeletedUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $table = 'lbaw2544.post';

    public $timestamps = false;

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

    public function tags()
    {
        return $this->belongsToMany(User::class, 'lbaw2544.post_tag', 'postid', 'userid')
            ->withPivot('createdat')
            ->orderBy('lbaw2544.post_tag.createdat', 'asc');
    }

    public function getAuthorAttribute()
    {
        $user = $this->user;
        if ($user && $user->isdeleted) {
            return DeletedUser::getDeletedUserPlaceholder();
        }
        return $user;
    }

    public static function getPostsWithDetails($currentUserId = null)
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
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.book b WHERE b.mediaId = m.id) THEN 'book'
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.film f WHERE f.mediaId = m.id) THEN 'movie'
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.music mu WHERE mu.mediaId = m.id) THEN 'music'
                END as media_type,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM lbaw2544.post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            LEFT JOIN lbaw2544.review r ON p.id = r.postId
            LEFT JOIN lbaw2544.media m ON r.mediaId = m.id
            ORDER BY p.id DESC
        ';

        $params = $currentUserId ? [$currentUserId] : [];

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
            FROM lbaw2544.post_tag pt
            JOIN lbaw2544.users u ON pt.userId = u.id
            WHERE pt.postId IN (" . implode(',', array_fill(0, count($postIds), '?')) . ")
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
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.book b WHERE b.mediaId = m.id) THEN 'book'
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.film f WHERE f.mediaId = m.id) THEN 'movie'
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.music mu WHERE mu.mediaId = m.id) THEN 'music'
                END as media_type,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) > 0 FROM lbaw2544.post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            LEFT JOIN lbaw2544.review r ON p.id = r.postId
            LEFT JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE p.userId IN (
                SELECT userId2 FROM lbaw2544.friendship WHERE userId1 = ?
                UNION
                SELECT userId1 FROM lbaw2544.friendship WHERE userId2 = ?
            )
            ORDER BY p.id DESC
        ";

        $posts = DB::select($sql, [$currentUserId, $currentUserId, $currentUserId]);
        
        return self::attachTagsToPostData($posts);
    }

    public static function getMovieReviewPosts($currentUserId = null)
    {
        $sql = "
            SELECT 
                p.id,
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
                'movie' as media_type,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM lbaw2544.post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE EXISTS (SELECT 1 FROM lbaw2544.film f WHERE f.mediaId = m.id)
            AND p.groupId IS NULL
            ORDER BY p.id DESC
        ';

        $params = $currentUserId ? [$currentUserId] : [];

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function getBookReviewPosts($currentUserId = null)
    {
        $sql = "
            SELECT 
                p.id,
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
                'book' as media_type,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM lbaw2544.post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE EXISTS (SELECT 1 FROM lbaw2544.book b WHERE b.mediaId = m.id)
            AND p.groupId IS NULL
            ORDER BY p.id DESC
        ';

        $params = $currentUserId ? [$currentUserId] : [];

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function getMusicReviewPosts($currentUserId = null)
    {
        $sql = "
            SELECT 
                p.id,
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
                'music' as media_type,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM lbaw2544.post_like pl WHERE pl.postId = p.id AND pl.userId = ?) as is_liked,' : 'FALSE as is_liked,') . '
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count,
                sp.imageUrl as image_url
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postId
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE EXISTS (SELECT 1 FROM lbaw2544.music mu WHERE mu.mediaId = m.id)
            AND p.groupId IS NULL
            ORDER BY p.id DESC
        ';

        $params = $currentUserId ? [$currentUserId] : [];

        $posts = DB::select($sql, $params);
        
        return self::attachTagsToPostData($posts);
    }

    public static function toggleLike($postId, $userId)
    {
        $post = self::find($postId);
        $user = User::find($userId);
        
        $existing = DB::table('lbaw2544.post_like')
            ->where('postid', $postId)
            ->where('userid', $userId)
            ->first();

        if ($existing) {
            DB::table('lbaw2544.post_like')
                ->where('postid', $postId)
                ->where('userid', $userId)
                ->delete();
            $liked = false;
        } else {
            DB::table('lbaw2544.post_like')->insert([
                'postid' => $postId,
                'userid' => $userId,
                'createdat' => now(),
            ]);
            $liked = true;
        }

        $likesCount = DB::table('lbaw2544.post_like')
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
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            JOIN lbaw2544.standard_post sp ON p.id = sp.postId
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
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postId = p.id) as likes_count,
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postId = p.id) as comments_count
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userId = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupId = g.id
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE p.userId = ?
            ORDER BY p.createdAt DESC
        ', [$userId]);
    }
}
