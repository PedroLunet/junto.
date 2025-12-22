<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchPostController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:date_asc,date_desc'],
        ]);

        $search = $request->input('query', '') ?? "";
        $sort = $request->input('sort', 'date_desc');
        $currentUserId = Auth::id();

        $query = Post::with(['user', 'group', 'standardPost', 'review.media.book', 'review.media.film', 'review.media.music', 'tags'])
            ->withCount(['likes', 'comments'])
            ->whereHas('user', function ($q) {
                $q->where('isdeleted', false)->where('isblocked', false);
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('standardPost', function ($q) use ($search) {
                    $q->where('text', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('review', function ($q) use ($search) {
                    $q->where('content', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('username', 'ILIKE', "%{$search}%");
                });
            });
        }

        if ($sort === 'date_asc') {
            $query->orderBy('createdat', 'asc');
        } else {
            $query->orderBy('createdat', 'desc');
        }

        if ($currentUserId) {
             $query->withExists(['likes as is_liked' => function ($q) use ($currentUserId) {
                $q->where('userid', $currentUserId);
            }]);
        }

        $posts = $query->get();

        // Transform to match the view's expected format
        $posts = $posts->map(function ($post) {
            $transformedPost = (object) [
                'id' => $post->id,
                'created_at' => $post->createdat,
                'author_name' => $post->user->name,
                'username' => $post->user->username,
                'author_image' => $post->user->profilepicture,
                'group_name' => $post->group ? $post->group->name : null,
                'groupid' => $post->groupid,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'is_liked' => $post->is_liked ?? false,
                'tagged_users' => $post->tags->map(function($user) {
                    return (object) [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username
                    ];
                })
            ];

            if ($post->standardPost) {
                $transformedPost->content = $post->standardPost->text;
                $transformedPost->image_url = $post->standardPost->imageurl;
                $transformedPost->post_type = 'standard';
            } elseif ($post->review) {
                $transformedPost->content = $post->review->content;
                $transformedPost->rating = $post->review->rating;
                $transformedPost->post_type = 'review';
                
                if ($post->review->media) {
                    $media = $post->review->media;
                    $transformedPost->media_title = $media->title;
                    $transformedPost->media_poster = $media->coverimage;
                    $transformedPost->media_year = $media->releaseyear;
                    $transformedPost->media_creator = $media->creator;
                    
                    if ($media->book) {
                        $transformedPost->media_type = 'book';
                    } elseif ($media->music) {
                        $transformedPost->media_type = 'music';
                    } else {
                        $transformedPost->media_type = 'movie';
                    }
                }
            }
            
            return $transformedPost;
        });

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
