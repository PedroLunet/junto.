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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($friends as $friend)
                    <div
                        class="bg-white shadow-sm rounded-3xl overflow-hidden hover:shadow-md transition-shadow border border-gray-100">
                        <div class="flex items-center justify-between p-6">
                            <a href="{{ route('profile.show', $friend->username) }}"
                                class="flex items-center space-x-4 flex-1">
                                <div class="w-32 h-32 rounded-full bg-[#F1EBF4] flex items-center justify-center shrink-0">
                                    <span
                                        class="text-4xl font-bold text-[#820273]">{{ substr($friend->name, 0, 1) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-3xl text-gray-900 truncate">{{ $friend->name }}</h3>
                                    <p class="text-gray-500 text-2xl truncate">@<!-- -->{{ $friend->username }}</p>
                                </div>
                            </a>
                            <form action="{{ route('friends.unfriend', $friend->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to unfriend {{ $friend->name }}?')"
                                class="ml-4">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" class="px-4 py-1.5 text-xl">
                                    Unfriend
                                </x-button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
