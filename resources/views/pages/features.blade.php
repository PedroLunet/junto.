@extends('layouts.app')

@section('title', 'Main Features')

@section('content')
    <div class="container mx-auto min-h-screen flex items-center justify-center">
        <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-3 gap-6 bento-grid">
            <!-- Box 1: Large vertical -->
            <div
                class="bg-purple-100 rounded-2xl p-10 row-span-2 col-span-2 flex flex-col items-start justify-center shadow">
                <h1 class="text-4xl font-extrabold mb-4 text-gray-900">One Hub for All Posts & Reviews</h1>
                <p class="text-2xl font-normal text-gray-700">
                    Share and read both regular posts and reviews for movies, books, and music in a single timeline. No need
                    to switch between platforms to track what you watch, read, listen to, or simply want to talk
                    about... Everything is in one place.
                </p>
            </div>
            <!-- Box 2: Top right -->
            <div class="bg-pink-100 rounded-2xl p-10 col-span-2 flex flex-col items-start justify-center shadow">
                <h1 class="text-4xl font-extrabold mb-4 text-gray-900">Show Off Your Top 3 Favorites</h1>
                <p class="text-2xl font-normal text-gray-700">
                    Personalize your profile by selecting your favorite book, movie, and song. Your top picks are always
                    visible on your profile, making it easy for others to discover your tastes and start conversations
                    around what you love most.
                </p>
            </div>
            <!-- Box 3: Middle right -->
            <div class="bg-yellow-100 rounded-2xl p-10 flex flex-col items-start justify-center shadow">
                <h1 class="text-4xl font-extrabold mb-4 text-gray-900">Find Exactly What Matters</h1>
                <p class="text-2xl font-normal text-gray-700">
                    Don't just scroll... Search. Use powerful filters to find users,
                    posts, and groups by specific attributes or interests with pinpoint accuracy.
                </p>
            </div>
            <!-- Box 4: Middle far right -->
            <div class="bg-green-100 rounded-2xl p-10 flex flex-col items-start justify-center shadow">
                <h1 class="text-4xl font-extrabold mb-4 text-gray-900">A Trusted Space</h1>
                <p class="text-2xl font-normal text-gray-700">
                    A platform built on respect. Advanced reporting features,
                    contextual error handling, and a dedicated admin team ensure a welcoming environment for everyone.
                </p>
            </div>
            <!-- Box 5: Bottom left -->
            <div class="bg-orange-100 rounded-2xl p-10 col-span-2 flex flex-col items-start justify-center shadow">
                <h1 class="text-4xl font-extrabold mb-4 text-gray-900">Connect & Collaborate</h1>
                <p class="text-2xl font-normal text-gray-700">
                    Build your network through friendships or organize around
                    shared hobbies. Create groups, tag friends in discussions, and find people who love what you love.
                </p>
            </div>
            <!-- Box 6: Bottom right -->
            <div class="bg-blue-100 rounded-2xl p-10 col-span-2 flex flex-col items-start justify-center shadow">
                <h1 class="text-4xl font-extrabold mb-4 text-gray-900">Your Feed, Your Way</h1>
                <p class="text-2xl font-normal text-gray-700">
                    Toggle between a global public view, a personalized timeline
                    with only your friends' posts, or just your friends' feeds. Need focus? Snooze notifications to browse
                    without distraction.
                </p>
            </div>
        </div>
    </div>
@endsection
