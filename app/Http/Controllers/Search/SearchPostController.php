<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchPostController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:date_asc,date_desc'],
            'post_type' => ['nullable', 'string', 'in:all,standard,review'],
        ]);

        $search = $request->input('query', '') ?? "";
        $sort = $request->input('sort', 'date_desc');
        $postType = $request->input('post_type', 'all');
        $currentUserId = Auth::id();

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
            WHERE u.isdeleted = false AND u.isblocked = false
        ";

        $params = [$currentUserId];

        if (!empty($search)) {
            $sql .= " AND (
                COALESCE(sp.text, r.content) ILIKE ? 
                OR u.name ILIKE ?
                OR u.username ILIKE ?
            )";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($postType === 'standard') {
            $sql .= " AND sp.postId IS NOT NULL";
        } elseif ($postType === 'review') {
            $sql .= " AND r.postId IS NOT NULL";
        }

        if ($sort === 'date_asc') {
            $sql .= " ORDER BY p.createdAt ASC";
        } else {
            $sql .= " ORDER BY p.createdAt DESC";
        }

        $posts = DB::select($sql, $params);
        $posts = Post::attachTagsToPostData($posts);

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'posts' => $posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'content' => $post->content,
                        'author_name' => $post->author_name,
                        'username' => $post->username,
                    ];
                })
            ]);
        }

        return view("pages.search.index", [
            'posts' => $posts,
            'sort' => $sort,
            'activeTab' => 'posts',
        ]);
    }
}
