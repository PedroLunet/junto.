@extends('layouts.app')

@section('content')
<div class="container">
    @auth
    <h3>{{ $user->name}}</h3>
    <p>@<span>{{ $user->username }}</span></p>
    @endauth

    @if(Auth::id() === $user->id)
        <p><em>This is your profile</em></p>
    @endif
</div>
@endsection