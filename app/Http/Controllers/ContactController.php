<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        // Validation and logic to handle the contact form submission would go here.
        // For now, we just redirect back with a success message.
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string',
        ]);

        return redirect()->route('contact.show')->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
