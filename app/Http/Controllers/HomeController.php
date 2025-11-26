<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Post;

class HomeController extends Controller
{
    public function index(): View
    {
        $posts = Post::getPostsWithDetails(auth()->id());
        return view('pages.home', compact('posts'));
    }

    public function getComments($id)
    {
        $comments = Post::getCommentsForPost($id);
        return response()->json($comments);
    }

    public function addComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = Post::addComment($id, auth()->id(), $request->input('content'));

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }

    public function toggleLike($id)
    {
        $result = Post::toggleLike($id, auth()->id());

        return response()->json([
            'success' => true,
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count']
        ]);
    }
}