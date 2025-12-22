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
            ->select('senderid', 'receiverid', 'sentat', 'content', 'isread')
            ->orderBy('sentat', 'desc')
            ->get();

        // map user id to latest message time
        $lastMessageDates = [];
        $lastMessageContents = [];
        $lastMessageSenders = [];
        $lastMessageReadStatus = [];

        foreach ($messages as $message) {
            $otherUserId = $message->senderid == $user->id ? $message->receiverid : $message->senderid;
            if (!isset($lastMessageDates[$otherUserId])) {
                $lastMessageDates[$otherUserId] = $message->sentat;
                $lastMessageContents[$otherUserId] = $message->content;
                $lastMessageSenders[$otherUserId] = $message->senderid;
                $lastMessageReadStatus[$otherUserId] = $message->isread;
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
            $friend->last_message_sender_id = $lastMessageSenders[$friend->id] ?? null;
            $friend->last_message_is_read = $lastMessageReadStatus[$friend->id] ?? false;
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

        $this->authorize('send', [Message::class, $friend]);

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

        if (request()->ajax()) {
            return view('components.messages.chat-box', compact('friend', 'messages'));
        }

        return view('pages.messages.show', array_merge(['friend' => $friend, 'messages' => $messages], $data));
    }

    public function store(Request $request, $userId)
    {
        $currentUser = Auth::user();
        $friend = User::findOrFail($userId);

        $this->authorize('send', [Message::class, $friend]);

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

        $notification = \App\Models\User\Notification::create([
            'message' => "New message from {$currentUser->name}",
            'isread' => false,
            'receiverid' => $userId,
            'createdat' => now(),
        ]);

        \App\Models\Notification\MessageNotification::create([
            'notificationid' => $notification->id,
            'messageid' => $message->id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'senderName' => $currentUser->name,
            ]);
        }

        return redirect()->route('messages.show', $userId);
    }

    public function destroy($userId)
    {
        $currentUser = Auth::user();
        $friend = User::findOrFail($userId);

        $this->authorize('deleteConversation', [Message::class, $friend]);

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
        $friend = User::findOrFail($userId);

        if ($currentUser->id == $userId) {
             return response()->json(['messages' => [], 'currentUserId' => $currentUser->id]);
        }

        $this->authorize('send', [Message::class, $friend]);

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
