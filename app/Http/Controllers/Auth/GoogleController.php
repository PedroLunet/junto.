<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect() {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle() {

        $google_user = Socialite::driver('google')->stateless()->user();
        $user = User::where('google_id', $google_user->getId())->first();
        
        // If the user does not exist, create one
        if (!$user) {
            // Check if email already exists
            $existingUser = User::where('email', $google_user->getEmail())->first();
            if ($existingUser) {
                // link google_id to existing user
                $existingUser->google_id = $google_user->getId();
                $existingUser->save();
                Auth::login($existingUser);
                redirect()->route('home');
            }

            // generate unique username
            $baseUsername = Str::slug($google_user->getName());
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Store the provided name, email, and Google ID in the database
            $new_user = User::create([
                'name' => $google_user->getName(),
                'username' => $username,
                'email' => $google_user->getEmail(),
                'google_id' => $google_user->getId(),
            ]);

            Auth::login($new_user);

        // Otherwise, simply log in with the existing user
        } else {
            Auth::login($user);
        }

        // after login, redirect to homepage
        return redirect()->route('home');
    }

}
