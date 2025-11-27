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
                @unless ($user->id == Auth::user()->id)
                    <x-user-card :user="$user" :showUnfriendButton="in_array($user->id, $friends)" :showBefriendButton="!in_array($user->id, $friends)" :friendButtonData="!in_array($user->id, $friends) ? $friendService->getFriendButtonData($user) : null" :unfriendRoute="route('friends.unfriend', $user->id)"
                        :confirmMessage="'Are you sure you want to unfriend ' . $user->name . '?'" />
                @endunless
            @empty
                <div class="col-span-full text-left text-gray-500 text-xl">
                    <p>No results match your filters, try adjusting them.</p>
                </div>
            @endforelse
        </div>
    </main>
@endsection
