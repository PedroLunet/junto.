<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Post\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchCommentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:date_asc,date_desc'],
        ]);

        $search = $request->input('query', '') ?? "";
        $sort = $request->input('sort', 'date_desc');

        $queryBuilder = Comment::query()
            ->select('comment.*')
            ->join('users', 'comment.userid', '=', 'users.id')
            ->join('post', 'comment.postid', '=', 'post.id')
            ->join('users as pu', 'post.userid', '=', 'pu.id')
            ->where('users.isdeleted', false)
            ->where('users.isblocked', false)
            ->where('pu.isdeleted', false)
            ->where('pu.isblocked', false);

        if (!empty($search)) {
            $queryBuilder->whereRaw('comment.content ILIKE ?', ["%{$search}%"]);
        }

        if ($sort === 'date_asc') {
            $queryBuilder->orderBy('comment.createdat', 'asc');
        } else {
            $queryBuilder->orderBy('comment.createdat', 'desc');
        }

        $comments = $queryBuilder->with(['user', 'post.user'])->get();

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'comments' => $comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'author_name' => $comment->author_name,
                        'username' => $comment->username,
                    ];
                })
            ]);
        }


        return view("pages.search.index", [
            'comments' => $comments,
            'sort' => $sort,
            'activeTab' => 'comments',
        ]);
    }
}
