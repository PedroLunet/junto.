@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-20 gap-10">
            <a href="{{ route('profile.show', auth()->user()->username) }}" class="mr-4 text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-3xl"></i>
            </a>
            <div>
                <h1 class="text-4xl font-bold text-gray-900">{{ auth()->user()->name }}</h1>
                <p class="text-gray-600 text-xl">@<!---->{{ auth()->user()->username }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($friends->isEmpty())
            <div class="bg-gray-100 p-6 rounded text-center">
                <p class="text-gray-600">You don't have any friends yet. Start by sending some friend requests!</p>
                <x-button onclick="window.location='{{ route('search.users') }}'" variant="primary" class="mt-4 px-6 py-2">
                    Find Friends
                </x-button>
            </div>
        @else
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Friends</h1>
            <div class="space-y-4">
                @foreach ($friends as $friend)
                    <x-user-card :user="$friend" :showUnfriendButton="true" :unfriendRoute="route('friends.unfriend', $friend->id)" :confirmMessage="'Are you sure you want to unfriend ' . $friend->name . '?'" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
