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
        $activeUsers = User::where('isblocked', false)
            ->get()
            ->reject(function ($user) {
                return self::isDeletedUser($user);
            })
            ->count();

        $totalPosts = Post::count();
        $standardPosts = Post::whereDoesntHave('review')->count();

        // count different types of reviews
        $musicReviews = \App\Models\Post\Review::whereHas('media', function ($q) {
            $q->whereHas('music');
        })->count();

        $movieReviews = \App\Models\Post\Review::whereHas('media', function ($q) {
            $q->whereHas('film');
        })->count();

        $bookReviews = \App\Models\Post\Review::whereHas('media', function ($q) {
            $q->whereHas('book');
        })->count();

        $pendingFriendRequests = FriendRequest::whereHas('request', function ($query) {
            $query->where('status', 'pending');
        })->count();
        $totalFriendships = Friendship::count();

        // Group stats
        $totalGroups = \App\Models\Group::count();

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
                'redirect_url' => route('admin.users', [], false)
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for update: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'redirect_url' => route('admin.users', [], false)
            ], 404);
        } catch (ValidationException $e) {
            Log::error('Validation failed: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => implode(' ', $e->validator->errors()->all()),
                'redirect_url' => route('admin.users', [], false)
            ], 422);
        } catch (\Exception $e) {
            Log::error('Exception updating user: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
                'redirect_url' => route('admin.users', [], false)
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
                    'message' => 'Cannot block an admin user.',
                    'redirect_url' => route('admin.users', [], false)
                ], 403);
            }

            $user->isblocked = true;
            $user->save();

            Log::info('User blocked successfully: ' . $user->username . ' (ID: ' . $user->id . ')');

            return response()->json([
                'success' => true,
                'message' => 'User blocked successfully',
                'redirect_url' => route('admin.users', [], false)
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for blocking: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'redirect_url' => route('admin.users', [], false)
            ], 404);
        } catch (\Exception $e) {
            Log::error('Exception blocking user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to block user: ' . $e->getMessage(),
                'redirect_url' => route('admin.users', [], false)
            ], 500);
        }
    }

    public function unblockUser($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->isblocked = false;
            $user->save();

            // auto approve any pending appeals if user is unblocked directly
            UnblockAppeal::where('userid', $user->id)
                ->where('status', 'pending')
                ->update(['status' => 'approved']);

            Log::info('User unblocked successfully: ' . $user->username . ' (ID: ' . $user->id . ')');
            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully',
                'redirect_url' => route('admin.users', [], false)
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for unblocking: ID ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'redirect_url' => route('admin.users', [], false)
            ]);
        } catch (\Exception $e) {
            Log::error('Exception unblocking user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user',
                'redirect_url' => route('admin.users', [], false)
            ]);
        }
    }

    public function listReports()
    {
        $reports = \App\Models\Post\Report::with([
            'post.user',
            'post.standardPost',
            'post.review.media.book',
            'post.review.media.film',
            'post.review.media.music',
            'post.group',
            'comment.user'
        ])
            ->orderBy('createdat', 'desc')
            ->get();



        $counts = [
            'all' => $reports->count(),
            'pending' => $reports->where('status', 'pending')->count(),
            'accepted' => $reports->where('status', 'accepted')->count(),
            'rejected' => $reports->where('status', 'rejected')->count(),
        ];

        return view('pages.admin.reports', compact('reports', 'counts'));
    }

    public function acceptReport($id)
    {
        try {
            DB::beginTransaction();

            $report = \App\Models\Post\Report::findOrFail($id);

            if ($report->postid) {
                Post::where('id', $report->postid)->delete();
            }

            if ($report->commentid) {
                \App\Models\Post\Comment::where('id', $report->commentid)->delete();
            }

            $report->update(['status' => 'accepted']);

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
            \App\Models\Post\Report::where('id', $id)->update(['status' => 'rejected']);

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
        $groups = \App\Models\Group::withCount(['members', 'posts'])
            ->with([
                'members' => function ($q) {
                    $q->where('membership.isowner', true);
                }
            ])
            ->orderBy('createdat', 'desc')
            ->get()
            ->map(function ($group) {
                $owner = $group->members->first();
                $group->owner_name = $owner ? $owner->name : 'Unknown';
                $group->owner_username = $owner ? $owner->username : 'unknown';
                return $group;
            });

        return view('pages.admin.groups', compact('groups'));
    }

    public function deleteGroup($id)
    {
        try {
            \App\Models\Group::findOrFail($id)->delete();

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

            $user->isblocked = false;
            $user->save();
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
                return redirect()->route('admin.users')->with('alert', [
                    'type' => 'error',
                    'title' => 'Unauthorized',
                    'message' => 'You are not authorized to delete users.'
                ]);
            }

            $request->validate([
                'password' => 'required|string',
            ]);

            // verify admin password
            if (!password_verify($request->password, $admin->passwordhash)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Incorrect password.'
                    ], 403);
                }
                return redirect()->route('admin.users')->with('alert', [
                    'type' => 'error',
                    'title' => 'Incorrect Password',
                    'message' => 'Incorrect password.'
                ]);
            }

            $user = User::findOrFail($id);
            if ($user->isadmin) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete another admin.'
                    ], 403);
                }
                return redirect()->route('admin.users')->with('alert', [
                    'type' => 'error',
                    'title' => 'Delete Failed',
                    'message' => 'Cannot delete another admin.'
                ]);
            }

            DB::transaction(function () use ($user) {
                DB::table('membership')
                    ->where('userid', $user->id)
                    ->delete();

                $user->markAsDeleted();
            });

            Log::info('Admin deleted user: ' . $user->username . ' (ID: ' . $user->id . ')');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.'
                ]);
            }
            return redirect()->route('admin.users')->with('alert', [
                'type' => 'success',
                'title' => 'User Deleted',
                'message' => 'User deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }
            return redirect()->route('admin.users')->with('alert', [
                'type' => 'error',
                'title' => 'User Not Found',
                'message' => 'User not found.'
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(' ', $e->validator->errors()->all())
                ], 422);
            }
            return redirect()->route('admin.users')->with('alert', [
                'type' => 'error',
                'title' => 'Validation Error',
                'message' => 'Validation failed: ' . implode(' ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            Log::error('Admin delete user error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('admin.users')->with('alert', [
                'type' => 'error',
                'title' => 'Delete Failed',
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ]);
        }
    }


    public static function isDeletedUser($user)
    {
        if (!$user)
            return false;
        // Check by isdeleted flag if available
        if (isset($user->isdeleted) && $user->isdeleted)
            return true;
        // Fallback: check by name or username pattern
        if (
            (isset($user->name) && $user->name === 'Deleted User') ||
            (isset($user->username) && preg_match('/^deleted_\\d+$/', $user->username))
        ) {
            return true;
        }
        return false;
    }
}
