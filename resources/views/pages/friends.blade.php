@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-2 sm:px-4 py-6 sm:py-8">
        <div class="flex flex-row items-center mb-10 sm:mb-20 gap-8 sm:gap-10">
            <a href="{{ route('profile.show', $user->username) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-lg sm:text-2xl"></i>
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-gray-600 text-base sm:text-xl">@<span>{{ $user->username }}</span></p>
            </div>
        </div>

        @if ($friends->isEmpty())
            <div class="p-4 sm:p-6 rounded text-center">
                @if (auth()->id() === $user->id)
                    <p class="text-gray-600 text-base sm:text-lg">You don't have any friends yet. Start by sending some
                        friend requests!</p>
                    <x-ui.button onclick="window.location='{{ route('search.users') }}'" variant="primary"
                        class="mt-4 px-4 sm:px-6 py-2 text-sm sm:text-base">
                        Find Friends
                    </x-ui.button>
                @else
                    <p class="text-gray-600 text-base sm:text-lg">{{ $user->name }} doesn't have any friends yet.</p>
                @endif
            </div>
        @else
            <h1 class="text-base sm:text-lg font-bold text-gray-900 mb-4 sm:mb-6">Friends</h1>
            <div class="space-y-3 sm:space-y-4">
                @foreach ($friends as $friend)
                    <x-ui.user-card :user="$friend" :showUnfriendButton="auth()->id() === $user->id" :showBefriendButton="auth()->id() !== $user->id" :friendButtonData="isset($friendsData) && isset($friendsData[$friend->id])
                        ? $friendsData[$friend->id]
                        : null"
                        :unfriendRoute="route('friends.unfriend', $friend->id)" :confirmMessage="'Are you sure you want to unfriend ' . $friend->name . '?'" />
                @endforeach
            </div>
        @endif
    </div>

    <x-ui.confirm />
@endsection
