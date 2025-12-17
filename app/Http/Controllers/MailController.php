<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Mail\MailModel;

class MailController extends Controller
{
    public function send(Request $request) {

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
            'resetUrl' => url('/reset-password'), // Add your actual reset password route
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('home')->with('status', 'Password reset link sent!');
    }

}
