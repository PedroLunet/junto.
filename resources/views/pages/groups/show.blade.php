@extends('layouts.app')

@section('content')
    <h1>{{ $group->name }}</h1>
    <p>{{ $group->description }}</p>

    @if ($group->members->contains(Auth::user()))
        <form method="POST" action="{{ route('groups.leave', $group) }}">
            @csrf
            <button type="submit">Leave Group</button>
        </form>
    @else
        <form method="POST" action="{{ route('groups.join', $group) }}">
            @csrf
            <button type="submit">Join Group</button>
        </form>
    @endif

    <h2>Members</h2>
    <ul>
        @foreach ($group->members as $member)
            <li>{{ $member->name }}</li>
        @endforeach
    </ul>
@endsection
