<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\FriendRequest;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
