<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Models\Post\Comment;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    public function index($postId)
    {
        $comments = Comment::getCommentsForPost($postId);
        return view('components.posts.comment.comments-list', compact('comments'))->render();
    }

    public function store(Request $request, $postId)
{
    $this->authorize('create', Comment::class);

    $request->validate([
        'content' => 'required|string|max:1000'
    ]);

    $comment = Comment::addComment($postId, auth()->id(), $request->input('content'));

    return response()->json([
        'success' => true,
        'message' => 'Comment posted successfully!',
        'comment' => $comment
    ], 201); 
}

    public function update(Request $request, $commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $this->authorize('update', $comment);

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        Comment::updateComment($commentId, $request->input('content'));

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully!'
        ]);
    }
}