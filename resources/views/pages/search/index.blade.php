@extends('layouts.app')

@section('content')
    <main class="w-full max-w-4xl mx-auto flex flex-col items-center gap-4 sm:gap-6 px-4 py-4 sm:py-6">
        <div>
            <h1 class="text-lg sm:text-2xl font-black text-center">Search for a user</h1>
            <p class="text-center text-sm sm:text-base">Start by typing your friend's name!</p>
        </div>

        <form method="get" class="flex flex-col gap-3 sm:gap-6 w-full">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-stretch sm:items-center w-full">
                <input value="{{ old('query', request('query')) }}" type="text" name="query" placeholder="Search by name, username, or bio..." class="flex-1 w-full h-10 border-2 rounded-lg pl-4"  />
                <x-ui.button class="h-10">Search</x-ui.button>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center w-full">
                <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                    <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Newest first</option>
                    <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Oldest first</option>
                </select>
            </div>
        </form>

        <div class="w-full flex flex-col gap-3">
            @forelse ($users as $user)
                @php
                    $isLoggedIn = Auth::check();
                    $isCurrentUser = $isLoggedIn && $user->id == Auth::id();
                    $isFriend = in_array($user->id, $friends);
                    $friendButtonData = !$isFriend && $isLoggedIn ? $friendService->getFriendButtonData($user) : null;
                @endphp

                @if (!$isCurrentUser)
                    <x-ui.user-card :user="$user" :showUnfriendButton="$isLoggedIn && $isFriend" :showBefriendButton="$isLoggedIn && !$isFriend" :friendButtonData="$friendButtonData"
                        :unfriendRoute="$isLoggedIn ? route('friends.unfriend', $user->id) : route('login')" :confirmMessage="$isLoggedIn ? 'Are you sure you want to unfriend ' . $user->name . '?' : ''" />
                @endif

            @empty
                <p>No users found.</p>
            @endforelse
        </div>
    </main>
@endsection
