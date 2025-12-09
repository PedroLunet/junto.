@extends('layouts.app')

@section('content')
    <h1>Create Group</h1>

    <form method="POST" action="{{ route('groups.store') }}">
        @csrf

        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            @error('name')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="description">Description</label>
            <textarea name="description" id="description">{{ old('description') }}</textarea>
            @error('description')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="isPrivate">Private</label>
            <input type="hidden" name="isPrivate" value="0">
            <input type="checkbox" name="isPrivate" id="isPrivate" value="1" {{ old('isPrivate') ? 'checked' : '' }}>
            @error('isPrivate')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Create Group</button>
    </form>
@endsection
