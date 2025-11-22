@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-10 py-10">
        <div class="flex items-start justify-between gap-8 mb-8">
            <!-- profile header -->
            <div class="flex items-center gap-6">
                @auth
                    <div
                        class="w-40 h-40 bg-gray-300 rounded-full flex items-center justify-center text-6xl font-bold text-gray-600 shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <div class="flex-1">
                        <h3 class="text-4xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                        <p class="text-2xl text-gray-600 mb-2">@<span>{{ $user->username }}</span></p>

                        @if (Auth::id() === $user->id)
                            <p class="text-xl italic text-gray-500">This is your profile</p>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- 3 favorites -->
            <div class="flex gap-8 mr-40">
                <!-- fav music -->
                @if ($user->favoriteBookMedia)
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">{{ $user->favoriteSongMedia->title }}</span>
                    </div>
                @else
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">[fav music]</span>
                    </div>
                @endif

                <!-- fav book -->
                @if ($user->favoriteBookMedia)
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">{{ $user->favoriteBookMedia->title }}</span>
                    </div>
                @else
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">[fav book]</span>
                    </div>
                @endif

                <!-- fav movie -->
                @if ($user->favoriteFilmMedia)
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">{{ $user->favoriteFilmMedia->title }}</span>
                    </div>
                @else
                    <div class="w-40 h-40 bg-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-gray-600 text-xl text-center px-2">[fav movie]</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- tabs -->
        <div class="flex gap-4 mb-6">
            <button id="posts-tab"
                class="flex-1 bg-gray-400 text-white py-3 px-6 rounded-lg text-xl font-semibold transition-colors hover:bg-gray-500">
                Posts
            </button>
            <button id="reviews-tab"
                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg text-xl font-semibold transition-colors">
                Reviews
            </button>
        </div>

        <!-- tab content (empty for now) -->
        <div id="posts-content" class="tab-content">
            <p>STANDARD POSTS!</p>
        </div>

        <div id="reviews-content" class="tab-content hidden">
            <p>REVIEWS!</p>
        </div>
    </div>

    <script>
        document.getElementById('posts-tab').addEventListener('click', function() {
            // switch active tab - change classes directly
            document.getElementById('posts-tab').className = 'flex-1 bg-gray-400 text-white py-3 px-6 rounded-lg text-xl font-semibold transition-colors hover:bg-gray-500';
            document.getElementById('reviews-tab').className = 'flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg text-xl font-semibold transition-colors';

            // switch content
            document.getElementById('posts-content').classList.remove('hidden');
            document.getElementById('reviews-content').classList.add('hidden');
        });

        document.getElementById('reviews-tab').addEventListener('click', function() {
            // switch active tab - change classes directly
            document.getElementById('reviews-tab').className = 'flex-1 bg-gray-400 text-white py-3 px-6 rounded-lg text-xl font-semibold transition-colors hover:bg-gray-500';
            document.getElementById('posts-tab').className = 'flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg text-xl font-semibold transition-colors';

            // switch content
            document.getElementById('reviews-content').classList.remove('hidden');
            document.getElementById('posts-content').classList.add('hidden');
        });
    </script>
@endsection
