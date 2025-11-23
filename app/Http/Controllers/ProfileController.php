<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProfileController extends Controller
{
    public function index(): RedirectResponse
    {
        // Redirect to username-based URL
        return redirect('/' . Auth::user()->username);
    }
    
    public function show($username): View
    {
        $user = User::with(['favoriteFilmMedia', 'favoriteBookMedia', 'favoriteSongMedia'])
                    ->where('username', $username)
                    ->firstOrFail();
        
        $standardPosts = Post::getUserStandardPosts($user->id);
        $reviewPosts = Post::getUserReviewPosts($user->id);

        // merge both types and sort by date
        $posts = collect($standardPosts)
                    ->merge($reviewPosts)
                    ->sortByDesc('created_at')
                    ->values()
                    ->all();

        $friendsCount = DB::selectOne("SELECT fn_get_friendship_count(?) as count", [$user->id])->count;
        $postsCount = DB::selectOne("SELECT fn_get_user_posts_count(?) as count", [$user->id])->count;

        return view('pages.profile', compact('user', 'posts', 'friendsCount', 'postsCount'));
    }
}