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
                r.commentid as comment_id,
                pu.username as post_author_username,
                pu.name as post_author_name,
                cu.username as comment_author_username,
                cu.name as comment_author_name
            FROM lbaw2544.report r
            LEFT JOIN lbaw2544.post p ON r.postid = p.id
            LEFT JOIN lbaw2544.users pu ON p.userid = pu.id
            LEFT JOIN lbaw2544.comment c ON r.commentid = c.id
            LEFT JOIN lbaw2544.users cu ON c.userid = cu.id
            ORDER BY r.createdat DESC
        ");

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
}
