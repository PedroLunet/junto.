@extends('layouts.app')

@section('content')
<main id="search-users-page">
    <h1>Search for a user</h1>
    <p>Start by typing your friend's name!</p>

    <form method="get">
        <input value="{{ old('query', request('query')) }}" type="text" name="query" />
        <button>Search</button>
    </form>

    <div id="user-list">
        @forelse ($users as $user)

                <div id="user-card">
                    <h3>{{ $user->name }}</h3>
                </div>

        @empty
        <div class="col-span-full text-left text-gray-500 text-sm">
            <p id="no-results">No results match your filters, try adjusting them.</p>
        </div>
        @endforelse
    </div>
</main>
@endsection