<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
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

        return view('pages.profile', compact('user', 'posts'));
    }
}