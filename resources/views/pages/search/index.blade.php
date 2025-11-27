@extends('layouts.app')

@section('content')
<main id="search-users-page">
    <h1>Search for a user</h1>
    <p>Start by typing your friend's name!</p>

    <form method="get">
        <input value="{{ old('query', request('query')) }}" type="text" name="query" />
        <x-button>Search</x-button>
    </form>

    <div id="user-list">
        @forelse ($users as $user)
        @unless ($user->id == Auth::user()->id)
        <x-user-card
            :user="$user"
            :showUnfriendButton="in_array($user->id, $friends)"
            :showBefriendButton="!in_array($user->id, $friends)"
            :friendButtonData="!in_array($user->id, $friends) ? $friendService->getFriendButtonData($user)  : null"
            :unfriendRoute="route('friends.unfriend', $user->id)"
            :confirmMessage="'Are you sure you want to unfriend ' . $user->name . '?'" />

        @endunless
        @empty
        <div class="col-span-full text-left text-gray-500 text-sm">
            <p id="no-results">No results match your filters, try adjusting them.</p>
        </div>
        @endforelse
    </div>
</main>
@endsection