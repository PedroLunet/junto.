<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\Post\Post;
use App\Models\User\FriendRequest;
use App\Models\User\Friendship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    // display the admin dashboard with stats
    public function dashboard()
    {
        // get stats
        $totalUsers = User::count();
        $activeUsers = User::where('isblocked', false)->count();

        $totalPosts = Post::count();
        $standardPosts = Post::whereDoesntHave('review')->count();

        // count different types of reviews
        $musicReviews = DB::select("
            SELECT COUNT(*) as count 
            FROM lbaw2544.post p
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE EXISTS (SELECT 1 FROM lbaw2544.music mu WHERE mu.mediaId = m.id)
        ")[0]->count ?? 0;

        $movieReviews = DB::select("
            SELECT COUNT(*) as count 
            FROM lbaw2544.post p
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE EXISTS (SELECT 1 FROM lbaw2544.film f WHERE f.mediaId = m.id)
        ")[0]->count ?? 0;

        $bookReviews = DB::select("
            SELECT COUNT(*) as count 
            FROM lbaw2544.post p
            JOIN lbaw2544.review r ON p.id = r.postId
            JOIN lbaw2544.media m ON r.mediaId = m.id
            WHERE EXISTS (SELECT 1 FROM lbaw2544.book b WHERE b.mediaId = m.id)
        ")[0]->count ?? 0;

        $pendingFriendRequests = FriendRequest::whereHas('request', function ($query) {
            $query->where('status', 'pending');
        })->count();
        $totalFriendships = DB::table('friendship')->count();

        $stats = [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'totalPosts' => $totalPosts,
            'standardPosts' => $standardPosts,
            'musicReviews' => $musicReviews,
            'movieReviews' => $movieReviews,
            'bookReviews' => $bookReviews,
            'pendingRequests' => $pendingFriendRequests,
            'totalFriendships' => $totalFriendships,
        ];

        return view('pages.admin.dashboard', compact('stats'));
    }

    // display the admin users page
    public function users()
    {
        $users = User::orderBy('createdat', 'desc')->get();
        return view('pages.admin.users', compact('users'));
    }

    // create a new user
    public function createUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'bio' => 'nullable|string|max:1000',
                'is_admin' => 'boolean'
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'passwordhash' => bcrypt($request->password),
                'bio' => $request->bio,
                'isadmin' => $request->boolean('is_admin', false),
                'isblocked' => false,
                'isprivate' => false,
                'createdat' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ', $e->errors());
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Exception creating user: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    // update a user
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'bio' => 'nullable|string|max:1000',
                'is_admin' => 'boolean'
            ]);

            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'bio' => $request->bio,
                'isadmin' => $request->boolean('is_admin', false),
            ]);

            Log::info('User updated successfully: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('User not found for update: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ', $e->errors());
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Exception updating user: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listReports()
    {
        $reports = DB::select("
            SELECT 
                r.id,
                r.createdat,
                r.reason,
                r.status,
                r.postid as post_id,
                r.commentid as comment_id
            FROM lbaw2544.report r
            ORDER BY r.createdat DESC
        ");

        // enrich each report with full post or comment data
        foreach ($reports as $report) {
            if ($report->post_id) {
                // fetch complete post data
                $post = DB::selectOne("
                    SELECT 
                        p.id,
                        p.createdat as created_at,
                        sp.text as content,
                        sp.imageurl as image_url,
                        u.id as author_id,
                        u.name as author_name,
                        u.username,
                        g.name as group_name,
                        (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postid = p.id) as likes_count,
                        (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postid = p.id) as comments_count
                    FROM lbaw2544.post p
                    JOIN lbaw2544.users u ON p.userid = u.id
                    LEFT JOIN lbaw2544.groups g ON p.groupid = g.id
                    LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postid
                    WHERE p.id = ?
                ", [$report->post_id]);

                if ($post) {
                    // Check if it's a review
                    $review = DB::selectOne("
                        SELECT 
                            r.rating,
                            r.content as review_content,
                            m.id as media_id,
                            m.title,
                            m.coverimage,
                            m.creator,
                            m.releaseyear,
                            CASE 
                                WHEN EXISTS (SELECT 1 FROM lbaw2544.music mu WHERE mu.mediaid = m.id) THEN 'music'
                                WHEN EXISTS (SELECT 1 FROM lbaw2544.film f WHERE f.mediaid = m.id) THEN 'movie'
                                WHEN EXISTS (SELECT 1 FROM lbaw2544.book b WHERE b.mediaid = m.id) THEN 'book'
                                ELSE 'unknown'
                            END as media_type
                        FROM lbaw2544.review r
                        JOIN lbaw2544.media m ON r.mediaid = m.id
                        WHERE r.postid = ?
                    ", [$report->post_id]);

                    if ($review) {
                        $post->is_review = true;
                        $post->rating = $review->rating;
                        $post->content = $review->review_content;
                        $post->media_title = $review->title;
                        $post->media_poster = $review->coverimage;
                        $post->media_creator = $review->creator;
                        $post->media_year = $review->releaseyear;
                        $post->media_type = $review->media_type;
                    } else {
                        $post->is_review = false;
                    }

                    $report->post = $post;
                }
            } elseif ($report->comment_id) {
                // fetch complete comment data
                $comment = DB::selectOne("
                    SELECT 
                        c.id,
                        c.content,
                        c.createdat as created_at,
                        u.id as author_id,
                        u.name as author_name,
                        u.username,
                        u.profilepicture as author_picture
                    FROM lbaw2544.comment c
                    JOIN lbaw2544.users u ON c.userid = u.id
                    WHERE c.id = ?
                ", [$report->comment_id]);

                if ($comment) {
                    $report->comment = $comment;
                }
            }
        }

        return view('pages.admin.reports', compact('reports'));
    }

    public function acceptReport($id)
    {
        try {
            DB::beginTransaction();

            // Get the report details
            $report = DB::selectOne("
                SELECT postid, commentid 
                FROM lbaw2544.report 
                WHERE id = ?
            ", [$id]);

            if ($report) {
                // Delete the reported post if it exists
                if ($report->postid) {
                    DB::table('lbaw2544.post')
                        ->where('id', $report->postid)
                        ->delete();
                }

                // Delete the reported comment if it exists
                if ($report->commentid) {
                    DB::table('lbaw2544.comment')
                        ->where('id', $report->commentid)
                        ->delete();
                }
            }

            // Update report status
            DB::table('lbaw2544.report')
                ->where('id', $id)
                ->update(['status' => 'accepted']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report accepted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Accept report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept report'
            ], 500);
        }
    }

    public function rejectReport($id)
    {
        try {
            // Update report status
            DB::table('lbaw2544.report')
                ->where('id', $id)
                ->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => 'Report rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Reject report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject report'
            ], 500);
        }
    }

    public function groups()
    {
        return view('pages.admin.groups');
    }
}
