<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
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
        
        return view('pages.profile', compact('user'));
    }
}