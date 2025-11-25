<?php

namespace App\Http\Controllers;

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

        $canViewPosts = Auth::check() ? Gate::allows('viewPosts', $user) : false;

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
                $transformedPost = (object) [
                    'id' => $post->id,
                    'created_at' => $post->createdat,
                    'author_name' => $post->user->name,
                    'username' => $post->user->username,
                    'likes_count' => 0,
                    'comments_count' => 0,
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

        return view('pages.profile', compact('user', 'posts', 'standardPosts', 'reviewPosts', 'friendsCount', 'postsCount', 'canViewPosts', 'friendButtonData'));
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
}
