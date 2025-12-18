<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // get friends to show in the list
        $friends = $user->friends();

        return view('pages.messages.index', compact('friends'));
    }

    public function show($userId)
    {
        $currentUser = Auth::user();
        $friend = User::findOrFail($userId);

        if (!$currentUser->isFriendsWith($userId)) {
            return redirect()->route('messages.index')->with('error', 'You can only message friends.');
        }

        // fetch messages between current user and friend
        $messages = Message::where(function ($query) use ($currentUser, $userId) {
            $query->where('senderId', $currentUser->id)
                  ->where('receiverId', $userId);
        })->orWhere(function ($query) use ($currentUser, $userId) {
            $query->where('senderId', $userId)
                  ->where('receiverId', $currentUser->id);
        })->orderBy('sentAt', 'asc')->get();

        // mark message as read
        Message::where('senderId', $userId)
            ->where('receiverId', $currentUser->id)
            ->where('isRead', false)
            ->update(['isRead' => true]);

        return view('pages.messages.show', compact('friend', 'messages'));
    }

    public function store(Request $request, $userId)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isFriendsWith($userId)) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You can only message friends.'], 403);
            }
            return back()->with('error', 'You can only message friends.');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'senderId' => $currentUser->id,
            'receiverId' => $userId,
            'content' => $request->input('content'),
            'sentAt' => now(),
            'isRead' => false,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'senderName' => $currentUser->name, // or username
            ]);
        }

        return redirect()->route('messages.show', $userId);
    }
}
