<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\FriendRequest;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;

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

        return view('admin.dashboard', compact('stats'));
    }

    // display the admin users page
    public function users()
    {
        return view('admin.users');
    }
}
