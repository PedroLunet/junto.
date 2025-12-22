<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User\User;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    public function verify(Request $request, $token)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid password reset link.']);
        }

        // Store token and email in session for the next request
        session(['password_reset_token' => $token, 'password_reset_email' => $email]);

        return redirect()->route('password.reset');
    }

    public function showResetForm(Request $request)
    {
        $token = session('password_reset_token');
        $email = session('password_reset_email');

        if (!$token || !$email) {
             return redirect()->route('login')->withErrors(['email' => 'Invalid or expired password reset link.']);
        }

        return view('pages.auth.passwords.reset-page')->with(
            ['token' => $token, 'email' => $email]
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'Invalid token or email.']);
        }
        
        // Check if token is expired (e.g. 60 minutes)
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
             DB::table('password_reset_tokens')->where('email', $request->email)->delete();
             return back()->withErrors(['email' => 'This password reset token has expired.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that e-mail address.']);
        }

        $user->passwordhash = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }
}
