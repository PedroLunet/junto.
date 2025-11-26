@extends('layouts.app')

@section('page-title', 'Music Search')

@section('content')
<div>
    <h3>Search Music</h3>

    <!-- Search Form -->
    <form method="GET" action="{{ route('music.search') }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search for songs, artists...">
        <x-button type="submit">Search</x-button>
    </form>

    <!-- Success Message -->
    @if(session('success'))
    <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
        {{ session('success') }}
    </div>
    @endif

    <!-- Search Results -->
    @if(request('q'))
    <h4>Search results for: "{{ request('q') }}"</h4>

    @if(count($songs) > 0)
    @foreach($songs as $song)
    <div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
        <!-- Album Cover -->
        @if($song['coverimage'])
        <img src="{{ $song['coverimage'] }}" alt="{{ $song['title'] }} cover" width="50" height="50">
        @endif

        <!-- Song Info -->
        <div>
            <strong>{{ $song['title'] }}</strong><br>
            by {{ $song['creator'] }}<br>
            {{ $song['releaseyear'] }}
        </div>

        <!-- Add to Database Button -->
        <form method="POST" action="{{ route('music.store') }}" style="display: inline;">
            @csrf
            <input type="hidden" name="title" value="{{ $song['title'] }}">
            <input type="hidden" name="creator" value="{{ $song['creator'] }}">
            <input type="hidden" name="releaseyear" value="{{ $song['releaseyear'] }}">
            <input type="hidden" name="coverimage" value="{{ $song['coverimage'] }}">

            <x-button type="submit">Add to Library</x-button>
        </form>
    </div>
    @endforeach
    @else
    <p>No songs found for "{{ request('q') }}"</p>
    @endif
    @else
    <p>Search for music to get started</p>
    @endif
</div>
@endsection