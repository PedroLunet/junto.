<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Models\Post\Comment;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    /**
     * Get comments for a post
     */
    public function index($postId)
    {
        $comments = Comment::getCommentsForPost($postId);
        // If needed, eager load author: $comments = $comments->load('author');
        return view('components.posts.comment.comments-list', compact('comments'))->render();
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
