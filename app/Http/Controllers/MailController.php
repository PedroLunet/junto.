<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Mail\MailModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MailController extends Controller
{
    public function send(Request $request) {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
            'resetUrl' => url('/reset-password/verify/' . $token . '?email=' . urlencode($request->email)),
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('home')->with('status', 'Password reset link sent!');
    }

}
