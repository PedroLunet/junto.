<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Post\Post;

class LoginController extends Controller
{

    /**
     * Show the login form.
     *
     * If the user is already authenticated, redirect them
     * to the cards dashboard instead of showing the form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            // redirect admin users to admin dashboard, regular users to home
            return Auth::user()->isadmin
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        } else {
            $posts = Post::getPostsWithDetails();
            return view('auth.login', compact('posts'));
        }
    }

    /**
     * Process an authentication attempt.
     *
     * Validates the incoming request, checks the provided
     * credentials, and logs the user in if successful.
     * The session is regenerated to protect against session fixation.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        // Validate the request data.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to authenticate and log in the user.
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Regenerate the session ID to prevent session fixation attacks.
            $request->session()->regenerate();

            // Redirect admin users to admin dashboard, regular users to home
            $redirectRoute = Auth::user()->isadmin ? 'admin.dashboard' : 'home';
            return redirect()->intended(route($redirectRoute));
        }

        // Authentication failed: return back with an error message.
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
