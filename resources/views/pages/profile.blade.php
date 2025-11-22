@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- profile header -->
    <div class="flex items-center gap-6 mb-8">
        @auth
        <div class="w-40 h-40 bg-gray-300 rounded-full flex items-center justify-center text-6xl font-bold text-gray-600 shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <div class="flex-1">
            <h3 class="text-4xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
            <p class="text-2xl text-gray-600 mb-2">@<span>{{ $user->username }}</span></p>

            @if(Auth::id() === $user->id)
            <p class="text-xl italic text-gray-500">This is your profile</p>
            @endif
        </div>
        @endauth
    </div>


    <div class="favorites">
        @if($user->favoriteFilmMedia)
        <div class="favorite-item">
            <strong>Favorite Film:</strong>
            {{ $user->favoriteFilmMedia->title }}
            by {{ $user->favoriteFilmMedia->creator }}
            ({{ $user->favoriteFilmMedia->releaseyear }})
        </div>
        @endif

        @if($user->favoriteBookMedia)
        <div class="favorite-item">
            <strong>Favorite Book:</strong>
            {{ $user->favoriteBookMedia->title }}
            by {{ $user->favoriteBookMedia->creator }}
            ({{ $user->favoriteBookMedia->releaseyear }})
        </div>
        @endif

        @if($user->favoriteSongMedia)
        <div class="favorite-item">
            <strong>Favorite Song:</strong>
            {{ $user->favoriteSongMedia->title }}
            by {{ $user->favoriteSongMedia->creator }}
            ({{ $user->favoriteSongMedia->releaseyear }})
        </div>
        @endif

        @if(!$user->favoriteFilmMedia && !$user->favoriteBookMedia && !$user->favoriteSongMedia)
        <p><em>No favorites set</em></p>
        @endif
    </div>
</div>
@endsection