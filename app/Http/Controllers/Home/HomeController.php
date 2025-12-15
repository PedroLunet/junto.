<?php

namespace App\Http\Controllers\Home;


use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\Post\Post;


class HomeController extends Controller
{
    public function index(): View
    {
        $posts = Post::getPostsWithDetails(auth()->id());
        $posts = array_filter($posts, function ($post) {
            return empty($post->groupid);
        });
        $posts = array_values($posts);

        return view('pages.home', [
            'posts' => $posts,
            'pageTitle' => 'Home',
        ]);
    }

    public function friendsFeed(): View
    {
        $posts = Post::getFriendsPostsWithDetails(auth()->id());
        $posts = array_filter($posts, function ($post) {
            return empty($post->groupid);
        });
        $posts = array_values($posts);

        return view('pages.home', [
            'posts' => $posts,
            'pageTitle' => 'Friends Feed',
        ]);
    }

    public function toggleLike($id)
    {
        $result = Post::toggleLike($id, auth()->id());

       

        return response()->json([
            'success' => true,
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count'],
        ]);
    }
}
