@extends('layouts.app')

@section('content')
<main class="w-full max-w-4xl mx-auto flex flex-col items-center gap-8 px-4 py-8">
    <div>
        <h1 class="text-4xl font-black text-center">Search for a user</h1>
        <p class="text-center">Start by typing your friend's name!</p>
    </div>

    <form method="get" class="flex flex-row gap-4 items-start w-full">
        <input value="{{ old('query', request('query')) }}" type="text" name="query" class="max-w-3/4 w-full" />
        <x-button>Search</x-button>
    </form>

    <div class="w-full flex flex-col gap-4">
        @forelse ($users as $user)

        @php
        $isLoggedIn = Auth::check();
        $isCurrentUser = $isLoggedIn && $user->id == Auth::id();
        $isFriend = in_array($user->id, $friends);
        $friendButtonData = (!$isFriend && $isLoggedIn)
        ? $friendService->getFriendButtonData($user)
        : null;
        @endphp

        @if (!$isCurrentUser)
        <x-user-card
            :user="$user"
            :showUnfriendButton="$isLoggedIn && $isFriend"
            :showBefriendButton="$isLoggedIn && !$isFriend"
            :friendButtonData="$friendButtonData"
            :unfriendRoute="$isLoggedIn ? route('friends.unfriend', $user->id) : route('login')"
            :confirmMessage="$isLoggedIn ? 'Are you sure you want to unfriend ' . $user->name . '?' : ''" />
        @endif

        @empty
        <p>No users found.</p>
        @endforelse
    </div>
</main>
@endsection