<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Post;
use App\Models\Friendship;
use App\Services\FriendService;
use App\Services\FavoriteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    protected $friendService;
    protected $favoriteService;

    public function __construct(FriendService $friendService, FavoriteService $favoriteService)
    {
        $this->friendService = $friendService;
        $this->favoriteService = $favoriteService;
    }

    public function index(): RedirectResponse
    {
        return redirect('/' . Auth::user()->username);
    }

    public function show($username): View
    {
        $user = User::with(['favoriteFilmMedia', 'favoriteBookMedia', 'favoriteSongMedia'])
            ->where('username', $username)
            ->firstOrFail();

        // Guests can view posts if profile is public
        if (!$user->isprivate) {
            $canViewPosts = true;
        } else {
            $canViewPosts = Auth::check() ? Gate::allows('viewPosts', $user) : false;
        }

        // get posts only if user can view them
        $posts = collect();
        $standardPosts = collect();
        $reviewPosts = collect();

        if ($canViewPosts) {
            // get all posts with relationships
            $allPosts = Post::with(['standardPost', 'review.media', 'user'])
                ->where('userid', $user->id)
                ->orderBy('createdat', 'desc')
                ->get();

            // transform the data to match the expected format
            $posts = $allPosts->map(function ($post) {
                // get actual likes and comments counts from database
                $likesCount = \Illuminate\Support\Facades\DB::select("
                    SELECT COUNT(*) as count FROM lbaw2544.post_like 
                    WHERE postId = ?
                ", [$post->id])[0]->count ?? 0;

                $commentsCount = \Illuminate\Support\Facades\DB::select("
                    SELECT COUNT(*) as count FROM lbaw2544.comment 
                    WHERE postId = ?
                ", [$post->id])[0]->count ?? 0;

                // check if current user liked this post
                $isLiked = false;
                if (Auth::check()) {
                    $likedCheck = \Illuminate\Support\Facades\DB::select("
                        SELECT COUNT(*) as count FROM lbaw2544.post_like 
                        WHERE postId = ? AND userId = ?
                    ", [$post->id, Auth::id()])[0]->count ?? 0;
                    $isLiked = $likedCheck > 0;
                }

                $transformedPost = (object) [
                    'id' => $post->id,
                    'created_at' => $post->createdat,
                    'author_name' => $post->user->name,
                    'username' => $post->user->username,
                    'likes_count' => $likesCount,
                    'comments_count' => $commentsCount,
                    'is_liked' => $isLiked,
                ];

                // standard post data
                if ($post->standardPost) {
                    $transformedPost->content = $post->standardPost->text;
                    $transformedPost->image_url = $post->standardPost->imageurl;
                    $transformedPost->post_type = 'standard';
                }

                // review data
                if ($post->review) {
                    $transformedPost->content = $post->review->content;
                    $transformedPost->rating = $post->review->rating;
                    $transformedPost->media_title = $post->review->media ? $post->review->media->title : 'Unknown Media';
                    $transformedPost->media_poster = $post->review->media ? $post->review->media->coverimage : null;
                    $transformedPost->media_creator = $post->review->media ? $post->review->media->creator : null;
                    $transformedPost->media_year = $post->review->media ? $post->review->media->releaseyear : null;

                    // Determine media type by checking related tables
                    if ($post->review->media) {
                        $mediaId = $post->review->media->id;
                        if (\App\Models\Book::where('mediaid', $mediaId)->exists()) {
                            $transformedPost->media_type = 'book';
                        } elseif (\App\Models\Music::where('mediaid', $mediaId)->exists()) {
                            $transformedPost->media_type = 'music';
                        } else {
                            $transformedPost->media_type = 'movie'; // default for films
                        }
                    } else {
                        $transformedPost->media_type = 'movie';
                    }

                    $transformedPost->post_type = 'review';
                }

                return $transformedPost;
            });

            // separate standard posts and reviews for tabs
            $standardPosts = $posts->filter(function ($post) {
                return $post->post_type === 'standard';
            });

            $reviewPosts = $posts->filter(function ($post) {
                return $post->post_type === 'review';
            });
        }

        $friendsCount = Friendship::where('userid1', $user->id)
            ->orWhere('userid2', $user->id)
            ->count();

        $postsCount = Post::where('userid', $user->id)->count();

        // friend button data using the service
        $friendButtonData = $this->friendService->getFriendButtonData($user);

        // get pending friend requests count for notification dot
        $pendingRequestsCount = 0;
        if (Auth::id() === $user->id) {
            $pendingRequestsCount = \App\Models\FriendRequest::whereHas('request.notification', function ($query) use ($user) {
                $query->where('receiverid', $user->id);
            })
                ->whereHas('request', function ($query) {
                    $query->where('status', 'pending');
                })
                ->count();
        }

        return view('pages.profile', compact('user', 'posts', 'standardPosts', 'reviewPosts', 'friendsCount', 'postsCount', 'canViewPosts', 'friendButtonData', 'pendingRequestsCount'));
    }

    public function removeFavorite(Request $request)
    {
        $request->validate([
            'type' => 'required|in:book,movie,music'
        ]);

        $result = $this->favoriteService->removeFavorite($request->input('type'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json($result);
    }

    public function addFavorite(Request $request)
    {
        $request->validate([
            'type' => 'required|in:book,movie,music',
            'title' => 'required|string|max:255',
            'creator' => 'nullable|string|max:255',
            'releaseYear' => 'nullable|integer|min:1000|max:' . (date('Y') + 10),
            'coverImage' => 'nullable|url|max:500',
        ]);

        $result = $this->favoriteService->addFavorite($request->all());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json($result);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'bio' => 'nullable|string|max:1000'
        ]);

        try {
            // Update user data using direct DB query
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => $request->input('name'),
                    'username' => $request->input('username'),
                    'bio' => $request->input('bio')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'redirect_url' => '/' . $request->input('username') // redirect to new username if changed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your profile'
            ], 500);
        }
    }
}
