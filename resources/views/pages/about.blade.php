@extends('layouts.app')

@section('title', 'About Us')

@section('content')
    <div class="flex flex-col w-full">
        <!-- Hero Section -->
        <div class="w-full bg-gray-50 py-12 md:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 mb-4 md:mb-6">About junto.</h1>
                <p class="text-lg sm:text-xl md:text-2xl lg:text-3xl text-gray-700 leading-relaxed">
                    Bringing people together through shared passions for movies, music, and books.
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12 py-12 md:py-16 lg:py-20 space-y-12 md:space-y-16 lg:space-y-20">
                    <!-- Mission Section -->
                    <section>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-5xl font-bold text-gray-900 mb-4 md:mb-6">Our Mission</h2>
                        <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-gray-700 leading-relaxed">
                            junto. connects entertainment enthusiasts by creating a vibrant community where people can
                            share and discover what they love. Whether it's a film that moved you, a song that resonates,
                            or a book that changed your perspective, junto. is the place to celebrate these moments with friends.
                        </p>
                    </section>

                    <!-- Features Section -->
                    <section>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-5xl font-bold text-gray-900 mb-6 md:mb-8">What We Offer</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
                            <div class="bg-gray-50 p-6 sm:p-7 md:p-8 rounded-2xl shadow-lg">
                                <div class="text-4xl sm:text-5xl mb-4">🎬</div>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Movies</h3>
                                <p class="text-base sm:text-lg text-gray-700">
                                    Review and discuss films, rate performances, and share your favorite movie moments with your friends.
                                </p>
                            </div>
                            <div class="bg-gray-50 p-6 sm:p-7 md:p-8 rounded-2xl shadow-lg">
                                <div class="text-4xl sm:text-5xl mb-4">🎵</div>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Music</h3>
                                <p class="text-base sm:text-lg text-gray-700">
                                    Explore music recommendations, share your favorite tracks, and connect with people who have similar tastes.
                                </p>
                            </div>
                            <div class="bg-gray-50 p-6 sm:p-7 md:p-8 rounded-2xl shadow-lg">
                                <div class="text-4xl sm:text-5xl mb-4">📚</div>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Books</h3>
                                <p class="text-base sm:text-lg text-gray-700">
                                    Discuss literature, write reviews, and build your reading list with recommendations from friends.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Community Section -->
                    <section>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-5xl font-bold text-gray-900 mb-4 md:mb-6">Our Community</h2>
                        <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-gray-700 leading-relaxed mb-6 md:mb-8">
                            junto. is built on the belief that entertainment is better when shared. Our community is a space for authentic
                            conversations, genuine recommendations, and meaningful connections between people who appreciate culture and creativity.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div class="border-l-4 border-[#F75C03] pl-4 md:pl-6">
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Connect</h3>
                                <p class="text-base sm:text-lg text-gray-700">
                                    Build friendships based on shared interests and discover new perspectives through conversations with like-minded people.
                                </p>
                            </div>
                            <div class="border-l-4 border-[#820263] pl-4 md:pl-6">
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Discover</h3>
                                <p class="text-base sm:text-lg text-gray-700">
                                    Explore content recommendations from your friends and expand your entertainment horizons.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Call to Action -->
                    <section class="py-8 md:py-12 px-4 md:px-8 lg:px-12 bg-gradient-to-r from-[#F75C03] via-[#820263] to-[#291720] rounded-2xl text-white text-center">
                        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-5xl font-bold mb-4 md:mb-6">Ready to Join?</h2>
                        <p class="text-base sm:text-lg md:text-xl lg:text-2xl mb-6 md:mb-8">
                            Start connecting with friends who share your passion for entertainment.
                        </p>
                        @auth
                            <x-ui.button href="{{ route('friends-feed') }}" variant="special" class="text-lg md:text-xl lg:text-2xl font-medium">
                                Go to Feed
                                </x-button>
                            @else
                                <x-ui.button href="{{ route('register') }}" variant="special" class="text-lg md:text-xl lg:text-2xl font-medium">
                                    Get Started
                                    </x-button>
                            @endauth
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
