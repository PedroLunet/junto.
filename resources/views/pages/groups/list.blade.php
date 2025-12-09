@extends('layouts.app')

@section('content')
    <h1>Groups</h1>

    <a href="{{ route('groups.create') }}">Create Group</a>

    <ul>
        @foreach ($groups as $group)
            <li>
                <a href="{{ route('groups.show', $group) }}">{{ $group->name }}</a>
            </li>
        @endforeach
    </ul>
@endsection
