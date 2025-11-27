@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">My Friends</h1>

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
            <div class="space-y-4">
                @foreach ($friends as $friend)
                    <x-user-card :user="$friend" :showUnfriendButton="true" :unfriendRoute="route('friends.unfriend', $friend->id)" :confirmMessage="'Are you sure you want to unfriend ' . $friend->name . '?'" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
