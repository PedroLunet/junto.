<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\Post\Post;
use App\Models\User\FriendRequest;
use App\Models\User\Friendship;
use App\Models\UnblockAppeal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use \Illuminate\Database\Eloquent\ModelNotFoundException;
use \Illuminate\Validation\ValidationException;

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

        // Group stats
        $totalGroups = DB::table('lbaw2544.groups')->count();

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
            'totalGroups' => $totalGroups,
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
        } catch (ValidationException $e) {
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
                'bio' => 'nullable|string|max:1000',
                'is_admin' => 'boolean'
            ]);

            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'bio' => $request->bio,
                'isadmin' => $request->boolean('is_admin', false),
            ]);

            Log::info('User updated successfully: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for update: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (ValidationException $e) {
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

    public function blockUser($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->isadmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot block an admin user'
                ], 403);
            }

            $user->update(['isblocked' => true]);

            Log::info('User blocked successfully: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'User blocked successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for blocking: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Exception blocking user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to block user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unblockUser($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->update(['isblocked' => false]);

            // auto approve any pending appeals if user is unblocked directly
            UnblockAppeal::where('userid', $user->id)
                ->where('status', 'pending')
                ->update(['status' => 'approved']);

            Log::info('User unblocked successfully: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for unblocking: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Exception unblocking user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user: ' . $e->getMessage()
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

        $counts = [
            'all' => count($reports),
            'pending' => count(array_filter($reports, fn($r) => $r->status === 'pending')),
            'accepted' => count(array_filter($reports, fn($r) => $r->status === 'accepted')),
            'rejected' => count(array_filter($reports, fn($r) => $r->status === 'rejected')),
        ];

        return view('pages.admin.reports', compact('reports', 'counts'));
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
        $groups = DB::select("
            SELECT 
                g.id,
                g.name,
                g.description,
                g.isprivate,
                g.icon,
                g.createdat,
                COUNT(DISTINCT m.userid) as members_count,
                COUNT(DISTINCT p.id) as posts_count,
                (SELECT u.name FROM lbaw2544.users u 
                 JOIN lbaw2544.membership mem ON u.id = mem.userid 
                 WHERE mem.groupid = g.id AND mem.isowner = true LIMIT 1) as owner_name,
                (SELECT u.username FROM lbaw2544.users u 
                 JOIN lbaw2544.membership mem ON u.id = mem.userid 
                 WHERE mem.groupid = g.id AND mem.isowner = true LIMIT 1) as owner_username
            FROM lbaw2544.groups g
            LEFT JOIN lbaw2544.membership m ON g.id = m.groupid
            LEFT JOIN lbaw2544.post p ON g.id = p.groupid
            GROUP BY g.id, g.name, g.description, g.isprivate, g.icon, g.createdat
            ORDER BY g.createdat DESC
        ");

        return view('pages.admin.groups', compact('groups'));
    }

    public function deleteGroup($id)
    {
        try {
            DB::delete("DELETE FROM lbaw2544.groups WHERE id = ?", [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete group error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group'
            ], 500);
        }
    }

    public function appeals()
    {
        $appeals = UnblockAppeal::with('user')
            ->orderBy('createdat', 'desc')
            ->get();

        $counts = [
            'all' => $appeals->count(),
            'pending' => $appeals->where('status', 'pending')->count(),
            'approved' => $appeals->where('status', 'approved')->count(),
            'rejected' => $appeals->where('status', 'rejected')->count(),
        ];

        return view('pages.admin.unblock-appeals', compact('appeals', 'counts'));
    }

    public function submitAppeal(Request $request)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:1000'
            ]);

            $user = Auth::user();

            $this->authorize('create', UnblockAppeal::class);

            if (!$user->isblocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not blocked'
                ], 400);
            }

            // Check for any pending appeal for this user (for current block)
            $pendingAppeal = UnblockAppeal::where('userid', $user->id)
                ->where('status', 'pending')
                ->first();
            if ($pendingAppeal) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending appeal'
                ], 400);
            }

            // Defensive: If user was unblocked and then blocked again, ensure all previous appeals are not pending
            if ($user->isblocked) {
                // If there are any old appeals still marked as pending, mark them as 'expired' (custom status)
                UnblockAppeal::where('userid', $user->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'expired']);
            }

            UnblockAppeal::create([
                'userid' => $user->id,
                'reason' => $request->reason,
                'status' => 'pending'
            ]);

            Log::info('Unblock appeal submitted by user: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'Appeal submitted successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Submit appeal error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit appeal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveAppeal($id)
    {
        try {
            DB::beginTransaction();

            $appeal = UnblockAppeal::findOrFail($id);
            $this->authorize('update', $appeal);
            
            $user = $appeal->user;

            $user->update(['isblocked' => false]);
            $appeal->update(['status' => 'approved']);

            DB::commit();

            Log::info('Appeal approved and user unblocked: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'Appeal approved and user unblocked successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve appeal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve appeal'
            ], 500);
        }
    }

    public function rejectAppeal(Request $request, $id)
    {
        try {
            $appeal = UnblockAppeal::findOrFail($id);
            $this->authorize('update', $appeal);

            $appeal->update([
                'status' => 'rejected',
                'adminnotes' => $request->input('adminNotes', '')
            ]);

            Log::info('Appeal rejected for user: ' . $appeal->user->username . ' (ID: ' . $appeal->user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'Appeal rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Reject appeal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject appeal'
            ], 500);
        }
    }


    // Display admin account security page
    public function accountSecurity()
    {
        $user = Auth::user();
        return view('pages.admin.account-security', compact('user'));
    }

    // Update admin account details
    public function updateAccountSecurity(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email']
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Account details updated successfully'
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed: ', $e->errors());
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update admin account error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account details'
            ], 500);
        }
    }

    // Change admin password
    public function changeAdminPassword(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            // Verify current password
            if (!password_verify($validated['current_password'], $user->passwordhash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            DB::table('users')
                ->where('id', $user->id)
                ->update(['passwordhash' => bcrypt($validated['new_password'])]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Change admin password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password'
            ], 500);
        }
    }

    // Validate admin password
    public function validatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        $isValid = password_verify($request->password, $user->passwordhash);

        return response()->json([
            'valid' => $isValid
        ]);
    }

    // Admin deletes a user: reassigns posts/comments to Deleted User, then deletes user
    public function deleteUser(Request $request, $id)
    {
        try {
            $admin = Auth::user();
            // only allow admins
            if (!$admin->isadmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }

            $request->validate([
                'password' => 'required|string',
            ]);

            // verify admin password
            if (!password_verify($request->password, $admin->passwordhash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password.'
                ], 422);
            }

            $user = User::findOrFail($id);
            if ($user->isadmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete another admin.'
                ], 403);
            }

            DB::transaction(function () use ($user) {
                DB::table('membership')
                    ->where('userid', $user->id)
                    ->delete();

                $user->markAsDeleted();
            });

            Log::info('Admin deleted user: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin delete user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }
}
