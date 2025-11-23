<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Post;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProfileController extends Controller
{
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

        return view('pages.profile', compact('user', 'posts', 'standardPosts', 'reviewPosts', 'friendsCount', 'postsCount', 'canViewPosts'));
    }
}