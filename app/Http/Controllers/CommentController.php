<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Get comments for a post
     */
    public function index($postId)
    {
        $comments = Comment::getCommentsForPost($postId);
        return response()->json($comments);
    }

    /**
     * Add a comment to a post
     */
    public function store(Request $request, $postId)
    {
        $this->authorize('create', Comment::class);

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = Comment::addComment($postId, auth()->id(), $request->input('content'));

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }
}
