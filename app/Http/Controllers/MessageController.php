<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    private function getSidebarData($user)
    {
        $friends = $user->friends();

        // get all messages involving the user
        $messages = Message::where('senderid', $user->id)
            ->orWhere('receiverid', $user->id)
            ->select('senderid', 'receiverid', 'sentat', 'content')
            ->orderBy('sentat', 'desc')
            ->get();

        // map user id to latest message time
        $lastMessageDates = [];
        $lastMessageContents = [];
        foreach ($messages as $message) {
            $otherUserId = $message->senderid == $user->id ? $message->receiverid : $message->senderid;
            if (!isset($lastMessageDates[$otherUserId])) {
                $lastMessageDates[$otherUserId] = $message->sentat;
                $lastMessageContents[$otherUserId] = $message->content;
            }
        }

        // split friends into active chats and others
        $activeChats = $friends->filter(function ($friend) use ($lastMessageDates) {
            return isset($lastMessageDates[$friend->id]);
        })->sortByDesc(function ($friend) use ($lastMessageDates) {
            return $lastMessageDates[$friend->id];
        });

        // attach last message to active chats
        foreach ($activeChats as $friend) {
            $friend->last_message = $lastMessageContents[$friend->id] ?? '';
        }

        $otherFriends = $friends->filter(function ($friend) use ($lastMessageDates) {
            return !isset($lastMessageDates[$friend->id]);
        })->sortBy('name');

        return compact('activeChats', 'otherFriends');
    }

    public function index()
    {
        $user = Auth::user();
        $data = $this->getSidebarData($user);
        
        return view('pages.messages.index', $data);
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
            $query->where('senderid', $currentUser->id)
                  ->where('receiverid', $userId);
        })->orWhere(function ($query) use ($currentUser, $userId) {
            $query->where('senderid', $userId)
                  ->where('receiverid', $currentUser->id);
        })->orderBy('sentat', 'asc')->get();

        // mark message as read
        Message::where('senderid', $userId)
            ->where('receiverid', $currentUser->id)
            ->where('isread', false)
            ->update(['isread' => true]);

        $data = $this->getSidebarData($currentUser);
        
        
        if (!$data['activeChats']->contains('id', $friend->id)) {
             // remove from otherFriends if present
             $data['otherFriends'] = $data['otherFriends']->reject(function ($f) use ($friend) {
                 return $f->id == $friend->id;
             });
             // add to activechats at the top
             $data['activeChats'] = $data['activeChats']->prepend($friend);
        }

        if (request()->ajax()) {
            return view('components.messages.chat-box', compact('friend', 'messages'));
        }

        return view('pages.messages.show', array_merge(['friend' => $friend, 'messages' => $messages], $data));
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
            'senderid' => $currentUser->id,
            'receiverid' => $userId,
            'content' => $request->input('content'),
            'sentat' => now(),
            'isread' => false,
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

    public function destroy($userId)
    {
        $currentUser = Auth::user();

        // where (sender = current AND receiver = other) or (sender = other AND receiver = current)
        Message::where(function ($query) use ($currentUser, $userId) {
            $query->where('senderid', $currentUser->id)
                  ->where('receiverid', $userId);
        })->orWhere(function ($query) use ($currentUser, $userId) {
            $query->where('senderid', $userId)
                  ->where('receiverid', $currentUser->id);
        })->delete();

        return redirect()->route('messages.index')->with('success', 'Conversation deleted.');
    }

    public function fetchMessages($userId)
    {
        $currentUser = Auth::user();

        if ($currentUser->id == $userId) {
             return response()->json(['messages' => [], 'currentUserId' => $currentUser->id]);
        }

        if (!$currentUser->isFriendsWith($userId)) {
            return response()->json(['error' => 'You can only message friends.'], 403);
        }

        $messages = Message::where(function ($query) use ($currentUser, $userId) {
            $query->where('senderid', $currentUser->id)
                  ->where('receiverid', $userId);
        })->orWhere(function ($query) use ($currentUser, $userId) {
            $query->where('senderid', $userId)
                  ->where('receiverid', $currentUser->id);
        })->orderBy('sentat', 'asc')->get();

        // Mark received messages as read
        Message::where('senderid', $userId)
            ->where('receiverid', $currentUser->id)
            ->where('isread', false)
            ->update(['isread' => true]);

        return response()->json([
            'messages' => $messages,
            'currentUserId' => $currentUser->id
        ]);
    }
}
