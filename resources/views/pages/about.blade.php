@extends('layouts.app')

@section('content')
    <div class="flex flex-col w-full">
        <!-- Hero Section -->
        <div class="w-full py-8 md:py-10 lg:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12">
                <h1 class="text-3xl sm:text-4xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 md:mb-4">About junto.
                </h1>
                <p class="text-base sm:text-lg md:text-lg lg:text-xl text-gray-700 leading-relaxed">
                    Bringing people together through shared passions for movies, music, and books.
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12 py-8 md:py-10 lg:py-12 space-y-8 md:space-y-10 lg:space-y-12">
                <!-- Mission Section -->
                <section>
                    <h2 class="text-2xl sm:text-3xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 md:mb-4">Our Mission
                    </h2>
                    <p class="text-base sm:text-base md:text-lg lg:text-lg text-gray-700 leading-relaxed">
                        junto. connects entertainment enthusiasts by creating a vibrant community where people can
                        share and discover what they love. Whether it's a film that moved you, a song that resonates,
                        or a book that changed your perspective, junto. is the place to celebrate these moments with
                        friends.
                    </p>
                </section>

                <!-- Features Section -->
                <section>
                    <h2 class="text-2xl sm:text-3xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-4 md:mb-6">What We
                        Offer</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5 lg:gap-6">
                        <div class="bg-gray-50 p-5 sm:p-5 md:p-6 rounded-2xl shadow-lg">
                            <div class="text-3xl sm:text-4xl mb-3">🎬</div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Movies</h3>
                            <p class="text-sm sm:text-base text-gray-700">
                                Review and discuss films, rate performances, and share your favorite movie moments with your
                                friends.
                            </p>
                        </div>
                        <div class="bg-gray-50 p-5 sm:p-5 md:p-6 rounded-2xl shadow-lg">
                            <div class="text-3xl sm:text-4xl mb-3">🎵</div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Music</h3>
                            <p class="text-sm sm:text-base text-gray-700">
                                Explore music recommendations, share your favorite tracks, and connect with people who have
                                similar tastes.
                            </p>
                        </div>
                        <div class="bg-gray-50 p-5 sm:p-5 md:p-6 rounded-2xl shadow-lg">
                            <div class="text-3xl sm:text-4xl mb-3">📚</div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Books</h3>
                            <p class="text-sm sm:text-base text-gray-700">
                                Discuss literature, write reviews, and build your reading list with recommendations from
                                friends.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Community Section -->
                <section>
                    <h2 class="text-2xl sm:text-3xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 md:mb-4">Our
                        Community</h2>
                    <p class="text-base sm:text-base md:text-lg lg:text-lg text-gray-700 leading-relaxed mb-4 md:mb-6">
                        junto. is built on the belief that entertainment is better when shared. Our community is a space for
                        authentic
                        conversations, genuine recommendations, and meaningful connections between people who appreciate
                        culture and creativity.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div class="border-l-4 border-[#F75C03] pl-3 md:pl-4">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Connect</h3>
                            <p class="text-sm sm:text-base text-gray-700">
                                Build friendships based on shared interests and discover new perspectives through
                                conversations with like-minded people.
                            </p>
                        </div>
                        <div class="border-l-4 border-[#820263] pl-3 md:pl-4">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Discover</h3>
                            <p class="text-sm sm:text-base text-gray-700">
                                Explore content recommendations from your friends and expand your entertainment horizons.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Call to Action -->
                <section
                    class="py-6 md:py-8 px-4 md:px-6 lg:px-8 bg-gradient-to-r from-[#F75C03] via-[#820263] to-[#291720] rounded-2xl text-white text-center">
                    <h2 class="text-2xl sm:text-3xl md:text-3xl lg:text-4xl font-bold mb-3 md:mb-4">Ready to Join?</h2>
                    <p class="text-base sm:text-base md:text-lg lg:text-lg mb-4 md:mb-6">
                        Start connecting with friends who share your passion for entertainment.
                    </p>
                    @auth
                        <x-ui.button href="{{ route('friends-feed') }}" variant="special"
                            class="text-base md:text-base lg:text-lg font-medium">
                            Go to Feed
                        </x-ui.button>
                    @else
                        <x-ui.button href="{{ route('register') }}" variant="special"
                            class="text-base md:text-base lg:text-lg font-medium">
                            Get Started
                        </x-ui.button>
                    @endauth
                </section>
            </div>
        </div>
    </div>
    </div>
@endsection
