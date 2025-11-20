@extends('layouts.app')

@section('content')
<div class="container">
    @auth
    <h3>{{ $user->name }}</h3>
    <p>@<span>{{ $user->username }}</span></p>
    @endauth

    @if(Auth::id() === $user->id)
        <p><em>This is your profile</em></p>
    @endif

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