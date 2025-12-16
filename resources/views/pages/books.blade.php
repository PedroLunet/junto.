@extends('layouts.app')

@section('page-title', 'Book Search')

@section('content')
    <div>
        <h3>Search Books</h3>

        <!-- Search Form -->
        <form method="GET" action="{{ route('books.search') }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search for books, authors...">
            <x-ui.button type="submit">Search</x-ui.button>
        </form>

        <!-- Success Message -->
        @if (session('success'))
            <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search Results -->
        @if (request('q'))
            <h4>Search results for: "{{ request('q') }}"</h4>

            @if (count($books) > 0)
                @foreach ($books as $book)
                    <div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
                        <!-- Book Cover -->
                        @if ($book['coverimage'])
                            <img src="{{ $book['coverimage'] }}" alt="{{ $book['title'] }} cover" width="50"
                                height="50">
                        @endif

                        <!-- Book Info -->
                        <div>
                            <strong>{{ $book['title'] }}</strong><br>
                            by {{ $book['creator'] }}<br>
                            @if ($book['releaseyear'])
                                {{ $book['releaseyear'] }}
                            @endif
                        </div>

                        <!-- Add to Database Button -->
                        <form method="POST" action="{{ route('books.store') }}" style="display: inline;">
                            @csrf
                            <input type="hidden" name="title" value="{{ $book['title'] }}">
                            <input type="hidden" name="creator" value="{{ $book['creator'] }}">
                            @if ($book['releaseyear'])
                                <input type="hidden" name="releaseyear" value="{{ $book['releaseyear'] }}">
                            @endif
                            @if ($book['coverimage'])
                                <input type="hidden" name="coverimage" value="{{ $book['coverimage'] }}">
                            @endif

                            <x-ui.button type="submit">Add to Library</x-ui.button>
                        </form>
                    </div>
                @endforeach
            @else
                <p>No books found for "{{ request('q') }}"</p>
            @endif
        @else
            <p>Search for books to get started</p>
        @endif
    </div>
@endsection
