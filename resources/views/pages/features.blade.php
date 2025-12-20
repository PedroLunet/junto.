@extends('layouts.app')

@section('title', 'Main Features')

@section('content')
    <div class="container mx-auto min-h-[calc(100vh-8rem)] flex items-center justify-center px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 lg:grid-rows-3 gap-6 bento-grid w-full max-w-7xl">
            <!-- Box 1: Large vertical -->
            <div
                class="bg-[#e4dcf9] rounded-3xl p-6 lg:p-8 lg:row-span-2 lg:col-span-2 flex flex-col items-start justify-center shadow transition-transform duration-300 ease-in-out hover:scale-105">
                <h1 class="text-xl lg:text-2xl font-extrabold mb-6 lg:mb-8 text-gray-900">One Hub for All Posts & Reviews
                </h1>
                <div class="flex gap-3 lg:gap-4 mb-6 lg:mb-8 w-full justify-center">
                    <div class="w-20 h-20 lg:w-32 lg:h-32">
                        <img src="{{ asset('illustration-book.svg') }}" alt="Book illustration"
                            class="w-full h-full object-contain">
                    </div>
                    <div class="w-20 h-20 lg:w-32 lg:h-32">
                        <img src="{{ asset('illustration-movie.svg') }}" alt="Movie illustration"
                            class="w-full h-full object-contain">
                    </div>
                    <div class="w-20 h-20 lg:w-32 lg:h-32">
                        <img src="{{ asset('illustration-music.svg') }}" alt="Music illustration"
                            class="w-full h-full object-contain">
                    </div>
                </div>
                <p class="text-sm font-normal text-gray-700">
                    Share and read both regular posts and reviews for movies, books, and music in a single timeline. No need
                    to switch between platforms to track what you watch, read, listen to, or simply want to talk
                    about... Everything is in one place.
                </p>
            </div>
            <!-- Box 2: Top right -->
            <div
                class="bg-[#fcdfe9] rounded-3xl p-6 lg:p-8 lg:col-span-2 flex flex-col items-start justify-end shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-xl lg:text-2xl font-extrabold mb-2 text-gray-900">Show Off Your Top 3 Favorites</h1>
                <p class="text-sm font-normal text-gray-700 pr-24 lg:pr-24">
                    Personalize your profile by selecting your favorite book, movie, and song. Your top picks are always
                    visible on your profile, making it easy for others to discover your tastes and start conversations
                    around what you love most.
                </p>
                <div class="absolute bottom-4 right-4 w-24 h-24 lg:w-32 lg:h-32">
                    <img src="{{ asset('illustration-3favs.svg') }}" alt="Top 3 favorites illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 3: Middle right -->
            <div
                class="bg-[#fef7db] rounded-3xl p-6 lg:p-8 flex flex-col items-start justify-start shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-xl lg:text-2xl font-extrabold mb-2 text-gray-900">Find Exactly What Matters</h1>
                <p class="text-sm font-normal text-gray-700 mb-20">
                    Use powerful filters to find users, posts, and groups by specific attributes with pinpoint accuracy.
                </p>
                <div class="absolute bottom-0 right-2 w-24 h-24 lg:w-32 lg:h-32 opacity-80">
                    <img src="{{ asset('illustration-search.svg') }}" alt="Search illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 4: Middle far right -->
            <div
                class="bg-[#eff7e4] rounded-3xl p-6 lg:p-8 flex flex-col items-start justify-end shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-xl lg:text-2xl font-extrabold mb-2 text-gray-900">A Trusted Space</h1>
                <p class="text-sm font-normal text-gray-700">
                    A platform built on respect with advanced reporting and dedicated moderation.
                </p>
                <div class="absolute top-4 right-4 w-24 h-24 lg:w-32 lg:h-32">
                    <img src="{{ asset('illustration-trust.svg') }}" alt="Trust illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 5: Bottom left -->
            <div
                class="bg-[#fde9dd] rounded-3xl p-6 lg:p-8 lg:col-span-2 flex flex-col items-start justify-center shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-xl lg:text-2xl font-extrabold mb-2 text-gray-900">Connect & Collaborate</h1>
                <p class="text-sm font-normal text-gray-700 pr-32 lg:pr-48">
                    Build your network through friendships or organize around shared hobbies. Create groups, tag friends in
                    discussions, and find people who love what you love.
                </p>
                <div class="absolute top-1/2 -translate-y-1/2 right-4 w-32 h-32 lg:w-40 lg:h-40">
                    <img src="{{ asset('illustration-friends.svg') }}" alt="Connect illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 6: Bottom right -->
            <div
                class="bg-[#eef2f5] rounded-3xl p-6 lg:p-8 lg:col-span-2 flex flex-col items-start justify-center shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-xl lg:text-2xl font-extrabold mb-2 text-gray-900">Your Feed, Your Way</h1>
                <p class="text-sm font-normal text-gray-700 pr-32 lg:pr-48">
                    Toggle between a global public view, a personalized timeline
                    with only your friends' posts, or just your friends' feeds.
                    Need focus? Snooze notifications to browse without distraction.
                </p>
                <div class="absolute top-1/2 -translate-y-1/2 right-4 w-24 h-24 lg:w-32 lg:h-32 opacity-80">
                    <img src="{{ asset('illustration-customize.svg') }}" alt="Customize illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
        </div>
    </div>
@endsection
