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
            'time_period' => ['nullable', 'string', 'in:all,last_24h,last_week,last_month'],
        ]);

        $search = $request->input('query', '') ?? "";
        $sort = $request->input('sort', 'date_desc');
        $timePeriod = $request->input('time_period', 'all');

        $sql = "
            SELECT 
                c.id,
                c.content,
                c.createdat as created_at,
                c.postid,
                u.name as author_name,
                u.username,
                u.profilepicture as author_image,
                p.userid as post_author_id,
                pu.name as post_author_name,
                pu.username as post_author_username,
                (SELECT COUNT(*) FROM lbaw2544.friendship f WHERE (f.userid1 = u.id OR f.userid2 = u.id)) as user_followers_count
            FROM lbaw2544.comment c
            JOIN lbaw2544.users u ON c.userid = u.id
            JOIN lbaw2544.post p ON c.postid = p.id
            JOIN lbaw2544.users pu ON p.userid = pu.id
            WHERE u.isdeleted = false AND u.isblocked = false
            AND pu.isdeleted = false AND pu.isblocked = false
        ";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND c.content ILIKE ?";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
        }

        if ($timePeriod === 'last_24h') {
            $sql .= " AND c.createdat >= NOW() - INTERVAL '1 day'";
        } elseif ($timePeriod === 'last_week') {
            $sql .= " AND c.createdat >= NOW() - INTERVAL '7 days'";
        } elseif ($timePeriod === 'last_month') {
            $sql .= " AND c.createdat >= NOW() - INTERVAL '1 month'";
        }

        if ($sort === 'date_asc') {
            $sql .= " ORDER BY c.createdat ASC";
        } else {
            $sql .= " ORDER BY c.createdat DESC";
        }

        $comments = DB::select($sql, $params);

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'comments' => array_map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'author_name' => $comment->author_name,
                        'username' => $comment->username,
                    ];
                }, $comments)
            ]);
        }

        return view("pages.search.index", [
            'comments' => $comments,
            'sort' => $sort,
            'activeTab' => 'comments',
        ]);
    }
}
