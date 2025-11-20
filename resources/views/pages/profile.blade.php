@extends('layouts.app')

@section('content')
<div class="container">
    @auth
    <h3>{{ Auth::user()->name }}</h3>
    <p>@<span>{{ Auth::user()->username }}</span></p>
    @endauth
</div>
@endsection