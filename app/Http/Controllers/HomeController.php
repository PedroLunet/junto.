<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Post;

class HomeController extends Controller
{
    public function index(): View
    {
        $posts = Post::getPostsWithDetails();
        return view('pages.home', compact('posts'));
    }

    public function getComments($id)
    {
        $comments = Post::getCommentsForPost($id);
        return response()->json($comments);
    }
}