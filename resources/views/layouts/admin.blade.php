@extends('layouts.base')

@section('navigation')
    <ul class="space-y-2">
        <li>
            <a href="{{ route('admin.dashboard') }}"
                class="block py-2 px-4 rounded  hover:bg-[#7a5466] hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-[#a17f8f]' : '' }}">
                Admin Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.users') }}"
                class="block py-2 px-4 rounded  hover:bg-[#7a5466] hover:text-white {{ request()->routeIs('admin.users') ? 'bg-[#a17f8f]' : '' }}">
                Users
            </a>
        </li>
        <li>
            <a href="{{ route('admin.groups') }}"
                class="block py-2 px-4 rounded  hover:bg-[#7a5466] hover:text-white {{ request()->routeIs('admin.groups') ? 'bg-[#a17f8f]' : '' }}">
                Groups
            </a>
        </li>
        <li>
            <a href="{{ route('admin.reports') }}"
                class="block py-2 px-4 rounded  hover:bg-[#7a5466] hover:text-white {{ request()->routeIs('admin.reports') ? 'bg-[#a17f8f]' : '' }}">
                Reports
            </a>
        </li>
        
    </ul>
@endsection

@section('modals')
    @auth
        <x-posts.create.create-regular-modal />
        <x-posts.create.create-movie-review-modal />
        <x-posts.create.create-book-review-modal />
        <x-posts.create.create-music-review-modal />
    @endauth
@endsection
