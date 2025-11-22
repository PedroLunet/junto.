@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-10 py-10">
        <div class="flex items-start justify-between gap-8 mb-8">
            <!-- profile header -->
            <div class="flex items-center gap-6">
                @auth
                    <div
                        class="w-40 h-40 bg-gray-300 rounded-full flex items-center justify-center text-6xl font-bold text-gray-600 shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <div class="flex-1">
                        <h3 class="text-4xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                        <p class="text-2xl text-gray-600 mb-2">@<span>{{ $user->username }}</span></p>

                        @if (Auth::id() === $user->id)
                            <p class="text-xl italic text-gray-500">This is your profile</p>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- 3 favorites -->
            <div class="flex gap-8 mr-40">
                <!-- fav music -->
                @if ($user->favoriteBookMedia)
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">{{ $user->favoriteSongMedia->title }}</span>
                    </div>
                @else
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">[fav music]</span>
                    </div>
                @endif

                <!-- fav book -->
                @if ($user->favoriteBookMedia)
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">{{ $user->favoriteBookMedia->title }}</span>
                    </div>
                @else
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">[fav book]</span>
                    </div>
                @endif

                <!-- fav movie -->
                @if ($user->favoriteFilmMedia)
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">{{ $user->favoriteFilmMedia->title }}</span>
                    </div>
                @else
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">[fav movie]</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- tabs -->
        <x-tabs :tabs="[
            'posts' => [
                'title' => 'Posts',
                'content' => '<p>STANDARD POSTS!</p>'
            ],
            'reviews' => [
                'title' => 'Reviews', 
                'content' => '<p>REVIEWS!</p>'
            ]
        ]" />
    </div>
@endsection
