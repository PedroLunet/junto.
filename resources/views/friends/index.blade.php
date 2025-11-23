@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">My Friends</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($friends->isEmpty())
        <div class="bg-gray-100 p-6 rounded text-center">
            <p class="text-gray-600">You don't have any friends yet. Start by sending some friend requests!</p>
            <button onclick="window.location='{{ route('search.users') }}'" class="inline-block mt-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded">
                Find Friends
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($friends as $friend)
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-16 h-16 rounded-full bg-purple-200 flex items-center justify-center">
                            @if($friend->profilepicture)
                                <img src="{{ asset($friend->profilepicture) }}" alt="{{ $friend->name }}" class="w-16 h-16 rounded-full object-cover">
                            @else
                                <span class="text-xl font-bold text-purple-700">{{ substr($friend->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">{{ $friend->name }}</h3>
                            <p class="text-gray-600 text-sm">@<!-- -->{{ $friend->username }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="window.location='{{ route('profile.show', $friend->username) }}'" class="flex-1 text-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                            View Profile
                        </button>
                        <form action="{{ route('friends.unfriend', $friend->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to unfriend {{ $friend->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                Unfriend
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
