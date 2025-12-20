@extends('layouts.app')

@section('title', 'Main Features')

@section('content')
    <div class="container mx-auto min-h-[calc(100vh-8rem)] flex items-center justify-center px-4 py-8">

        <!-- mobile carousel -->
        <div class="lg:hidden w-full max-w-md relative">
            <div class="carousel-container overflow-hidden mx-3">
                <div class="carousel-track flex transition-transform duration-300 ease-in-out">
                    <!-- Box 1 -->
                    <div
                        class="carousel-item min-w-full bg-[#e4dcf9] rounded-3xl p-6 flex flex-col items-start justify-center shadow min-h-[400px]">
                        <h1 class="text-xl font-extrabold mb-6 text-gray-900">One Hub for All Posts & Reviews</h1>
                        <div class="flex gap-3 mb-6 w-full justify-center">
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-book.svg') }}" alt="Book illustration"
                                    class="w-full h-full object-contain">
                            </div>
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-movie.svg') }}" alt="Movie illustration"
                                    class="w-full h-full object-contain">
                            </div>
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-music.svg') }}" alt="Music illustration"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                        <p class="text-sm font-normal text-gray-700">
                            Share and read both regular posts and reviews for movies, books, and music in a single timeline.
                            No need
                            to switch between platforms to track what you watch, read, listen to, or simply want to talk
                            about... Everything is in one place.
                        </p>
                    </div>
                    <!-- Box 2 -->
                    <div
                        class="carousel-item min-w-full bg-[#fcdfe9] rounded-3xl p-6 flex flex-col items-center justify-center shadow min-h-[400px]">
                        <h1 class="text-xl font-extrabold mb-6 text-gray-900 text-center">Show Off Your Top 3 Favorites</h1>
                        <div class="flex gap-3 mb-6 w-full justify-center">
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-3favs.svg') }}" alt="Top 3 favorites illustration"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                        <p class="text-sm font-normal text-gray-700 text-center">
                            Personalize your profile by selecting your favorite book, movie, and song. Your top picks are
                            always
                            visible on your profile, making it easy for others to discover your tastes and start
                            conversations
                            around what you love most.
                        </p>
                    </div>
                    <!-- Box 3 -->
                    <div
                        class="carousel-item min-w-full bg-[#fef7db] rounded-3xl p-6 flex flex-col items-center justify-center shadow min-h-[400px]">
                        <h1 class="text-xl font-extrabold mb-6 text-gray-900 text-center">Find Exactly What Matters</h1>
                        <div class="flex gap-3 mb-6 w-full justify-center">
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-search.svg') }}" alt="Search illustration"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                        <p class="text-sm font-normal text-gray-700 text-center">
                            Use powerful filters to find users, posts, and groups by specific attributes with pinpoint
                            accuracy.
                        </p>
                    </div>
                    <!-- Box 4 -->
                    <div
                        class="carousel-item min-w-full bg-[#eff7e4] rounded-3xl p-6 flex flex-col items-center justify-center shadow min-h-[400px]">
                        <h1 class="text-xl font-extrabold mb-6 text-gray-900 text-center">A Trusted Space</h1>
                        <div class="flex gap-3 mb-6 w-full justify-center">
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-trust.svg') }}" alt="Trust illustration"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                        <p class="text-sm font-normal text-gray-700 text-center">
                            A platform built on respect with advanced reporting and dedicated moderation.
                        </p>
                    </div>
                    <!-- Box 5 -->
                    <div
                        class="carousel-item min-w-full bg-[#fde9dd] rounded-3xl p-6 flex flex-col items-center justify-center shadow min-h-[400px]">
                        <h1 class="text-xl font-extrabold mb-6 text-gray-900 text-center">Connect & Collaborate</h1>
                        <div class="flex gap-3 mb-6 w-full justify-center">
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-friends.svg') }}" alt="Connect illustration"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                        <p class="text-sm font-normal text-gray-700 text-center">
                            Build your network through friendships or organize around shared hobbies. Create groups, tag
                            friends in discussions, and find people who love what you love.
                        </p>
                    </div>
                    <!-- Box 6 -->
                    <div
                        class="carousel-item min-w-full bg-[#eef2f5] rounded-3xl p-6 flex flex-col items-center justify-center shadow min-h-[400px]">
                        <h1 class="text-xl font-extrabold mb-6 text-gray-900 text-center">Your Feed, Your Way</h1>
                        <div class="flex gap-3 mb-6 w-full justify-center">
                            <div class="w-20 h-20">
                                <img src="{{ asset('illustration-customize.svg') }}" alt="Customize illustration"
                                    class="w-full h-full object-contain">
                            </div>
                        </div>
                        <p class="text-sm font-normal text-gray-700 text-center">
                            Toggle between a global public view, a personalized timeline
                            with only your friends' posts, or just your friends' feeds.
                            Need focus? Snooze notifications to browse without distraction.
                        </p>
                    </div>
                </div>
            </div>

            <!-- nav arrows -->
            <x-ui.icon-button id="prevBtn" variant="gray"
                class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 rounded-full shadow-lg">
                <i class="fa-solid fa-chevron-left"></i>
            </x-ui.icon-button>
            <x-ui.icon-button id="nextBtn" variant="gray"
                class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 rounded-full shadow-lg">
                <i class="fa-solid fa-chevron-right"></i>
            </x-ui.icon-button>

            <!-- dots indicator -->
            <div class="flex justify-center gap-2 mt-6">
                <span class="dot w-2 h-2 rounded-full bg-gray-800 cursor-pointer" data-index="0"></span>
                <span class="dot w-2 h-2 rounded-full bg-gray-300 cursor-pointer" data-index="1"></span>
                <span class="dot w-2 h-2 rounded-full bg-gray-300 cursor-pointer" data-index="2"></span>
                <span class="dot w-2 h-2 rounded-full bg-gray-300 cursor-pointer" data-index="3"></span>
                <span class="dot w-2 h-2 rounded-full bg-gray-300 cursor-pointer" data-index="4"></span>
                <span class="dot w-2 h-2 rounded-full bg-gray-300 cursor-pointer" data-index="5"></span>
            </div>
        </div>

        <!-- desktop bento grid -->
        <div class="hidden lg:grid lg:grid-cols-4 lg:grid-rows-3 gap-6 bento-grid w-full max-w-7xl">
            <!-- Box 1: Large vertical -->
            <div
                class="bg-[#e4dcf9] rounded-3xl p-8 row-span-2 col-span-2 flex flex-col items-start justify-center shadow transition-transform duration-300 ease-in-out hover:scale-105">
                <h1 class="text-2xl font-extrabold mb-8 text-gray-900">One Hub for All Posts & Reviews</h1>
                <div class="flex gap-4 mb-8 w-full justify-center">
                    <div class="w-32 h-32">
                        <img src="{{ asset('illustration-book.svg') }}" alt="Book illustration"
                            class="w-full h-full object-contain">
                    </div>
                    <div class="w-32 h-32">
                        <img src="{{ asset('illustration-movie.svg') }}" alt="Movie illustration"
                            class="w-full h-full object-contain">
                    </div>
                    <div class="w-32 h-32">
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
                class="bg-[#fcdfe9] rounded-3xl p-8 col-span-2 flex flex-col items-start justify-end shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-2xl font-extrabold mb-2 text-gray-900">Show Off Your Top 3 Favorites</h1>
                <p class="text-sm font-normal text-gray-700 pr-24">
                    Personalize your profile by selecting your favorite book, movie, and song. Your top picks are always
                    visible on your profile, making it easy for others to discover your tastes and start conversations
                    around what you love most.
                </p>
                <div class="absolute bottom-4 right-4 w-32 h-32">
                    <img src="{{ asset('illustration-3favs.svg') }}" alt="Top 3 favorites illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 3: Middle right -->
            <div
                class="bg-[#fef7db] rounded-3xl p-8 flex flex-col items-start justify-start shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-2xl font-extrabold mb-2 text-gray-900">Find Exactly What Matters</h1>
                <p class="text-sm font-normal text-gray-700 mb-20">
                    Use powerful filters to find users, posts, and groups by specific attributes with pinpoint accuracy.
                </p>
                <div class="absolute bottom-0 right-2 w-32 h-32 opacity-80">
                    <img src="{{ asset('illustration-search.svg') }}" alt="Search illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 4: Middle far right -->
            <div
                class="bg-[#eff7e4] rounded-3xl p-8 flex flex-col items-start justify-end shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-2xl font-extrabold mb-2 text-gray-900">A Trusted Space</h1>
                <p class="text-sm font-normal text-gray-700">
                    A platform built on respect with advanced reporting and dedicated moderation.
                </p>
                <div class="absolute top-4 right-4 w-32 h-32">
                    <img src="{{ asset('illustration-trust.svg') }}" alt="Trust illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 5: Bottom left -->
            <div
                class="bg-[#fde9dd] rounded-3xl p-8 col-span-2 flex flex-col items-start justify-center shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-2xl font-extrabold mb-2 text-gray-900">Connect & Collaborate</h1>
                <p class="text-sm font-normal text-gray-700 pr-48">
                    Build your network through friendships or organize around shared hobbies. Create groups, tag friends in
                    discussions, and find people who love what you love.
                </p>
                <div class="absolute top-1/2 -translate-y-1/2 right-4 w-20 h-20">
                    <img src="{{ asset('illustration-friends.svg') }}" alt="Connect illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
            <!-- Box 6: Bottom right -->
            <div
                class="bg-[#eef2f5] rounded-3xl p-8 col-span-2 flex flex-col items-start justify-center shadow transition-transform duration-300 ease-in-out hover:scale-105 relative overflow-hidden min-h-[250px]">
                <h1 class="text-2xl font-extrabold mb-2 text-gray-900">Your Feed, Your Way</h1>
                <p class="text-sm font-normal text-gray-700 pr-48">
                    Toggle between a global public view, a personalized timeline
                    with only your friends' posts, or just your friends' feeds.
                    Need focus? Snooze notifications to browse without distraction.
                </p>
                <div class="absolute top-1/2 -translate-y-1/2 right-4 w-32 h-32 opacity-80">
                    <img src="{{ asset('illustration-customize.svg') }}" alt="Customize illustration"
                        class="w-full h-full object-contain">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const track = document.querySelector('.carousel-track');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const dots = document.querySelectorAll('.dot');
                let currentIndex = 0;
                const totalSlides = 6;

                function updateCarousel() {
                    track.style.transform = `translateX(-${currentIndex * 100}%)`;

                    // update dots
                    dots.forEach((dot, index) => {
                        if (index === currentIndex) {
                            dot.classList.remove('bg-gray-300');
                            dot.classList.add('bg-gray-800');
                        } else {
                            dot.classList.remove('bg-gray-800');
                            dot.classList.add('bg-gray-300');
                        }
                    });
                }

                prevBtn.addEventListener('click', function() {
                    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                    updateCarousel();
                });

                nextBtn.addEventListener('click', function() {
                    currentIndex = (currentIndex + 1) % totalSlides;
                    updateCarousel();
                });

                dots.forEach(dot => {
                    dot.addEventListener('click', function() {
                        currentIndex = parseInt(this.dataset.index);
                        updateCarousel();
                    });
                });

                // swipe support
                let touchStartX = 0;
                let touchEndX = 0;

                track.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                });

                track.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchStartX - touchEndX > 50) {
                        // swipe left
                        currentIndex = (currentIndex + 1) % totalSlides;
                        updateCarousel();
                    } else if (touchEndX - touchStartX > 50) {
                        // swipe right
                        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                        updateCarousel();
                    }
                });
            });
        </script>
    @endpush
@endsection
