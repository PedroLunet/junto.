@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-20 py-10">
        <div class="flex items-start justify-between gap-10 md:gap-12 lg:gap-16 mb-10 md:mb-12">
            <!-- profile header -->
            <div class="flex items-center gap-8 md:gap-10">
                @auth
                    <div
                        class="w-54 h-54 md:w-60 md:h-60 lg:w-72 lg:h-72 bg-gray-300 rounded-full flex items-center justify-center text-6xl md:text-7xl lg:text-8xl font-bold text-gray-600 shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <div class="flex-1">
                        <h3 class="text-5xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-2">{{ $user->name }}</h3>
                        <p class="text-3xl md:text-4xl text-gray-600 mb-4">@<span>{{ $user->username }}</span></p>

                        <!-- friends and posts count -->
                        <div class="flex gap-8 md:gap-10 mb-4 md:mb-6">
                            <div>
                                <span class="font-bold text-gray-900 text-xl md:text-2xl">{{ $friendsCount }}</span>
                                <span class="text-gray-600 text-xl md:text-2xl">Friends</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-900 text-xl md:text-2xl">{{ $postsCount }}</span>
                                <span class="text-gray-600 text-xl md:text-2xl">Posts</span>
                            </div>
                        </div>

                        @if (Auth::id() === $user->id)
                            <p class="text-2xl md:text-3xl italic text-gray-500">This is your profile</p>
                        @else
                            <!-- Friend request button -->
                            <div class="mt-4 md:mt-6">
                                @include('components.friend-button', ['user' => $user])
                            </div>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- 3 favorites -->
            <div class="flex gap-8 md:gap-12 lg:gap-16">
                <!-- fav book -->
                <div class="w-36 h-54 md:w-40 md:h-60 lg:w-48 lg:h-72 bg-gray-300 flex items-center justify-center overflow-hidden"
                    @if ($user->favoriteBookMedia) title="{{ $user->favoriteBookMedia->title }}{{ $user->favoriteBookMedia->creator ? ' - ' . $user->favoriteBookMedia->creator : '' }}" @endif>
                    @if ($user->favoriteBookMedia)
                        @if ($user->favoriteBookMedia->coverimage && filter_var($user->favoriteBookMedia->coverimage, FILTER_VALIDATE_URL))
                            <img src="{{ $user->favoriteBookMedia->coverimage }}"
                                alt="{{ $user->favoriteBookMedia->title }}" class="w-full h-full object-cover">
                        @else
                            <span
                                class="text-gray-600 text-lg md:text-xl text-center px-2">{{ $user->favoriteBookMedia->title }}</span>
                        @endif
                    @else
                        <span class="text-gray-600 text-lg md:text-xl text-center px-2">[fav book]</span>
                    @endif
                </div>

                <!-- fav movie -->
                <div class="w-36 h-54 md:w-40 md:h-60 lg:w-48 lg:h-72 bg-gray-300 flex items-center justify-center overflow-hidden"
                    @if ($user->favoriteFilmMedia) title="{{ $user->favoriteFilmMedia->title }}{{ $user->favoriteFilmMedia->creator ? ' - ' . $user->favoriteFilmMedia->creator : '' }}" @endif>
                    @if ($user->favoriteFilmMedia)
                        @if ($user->favoriteFilmMedia->coverimage && filter_var($user->favoriteFilmMedia->coverimage, FILTER_VALIDATE_URL))
                            <img src="{{ $user->favoriteFilmMedia->coverimage }}"
                                alt="{{ $user->favoriteFilmMedia->title }}" class="w-full h-full object-cover">
                        @else
                            <span
                                class="text-gray-600 text-lg md:text-xl text-center px-2">{{ $user->favoriteFilmMedia->title }}</span>
                        @endif
                    @else
                        <span class="text-gray-600 text-lg md:text-xl text-center px-2">[fav movie]</span>
                    @endif
                </div>

                <!-- fav music -->
                <div class="w-48 h-56 md:w-56 md:h-56 lg:w-72 lg:h-72 bg-gray-300 flex items-center justify-center overflow-hidden"
                    @if ($user->favoriteSongMedia) title="{{ $user->favoriteSongMedia->title }}{{ $user->favoriteSongMedia->creator ? ' - ' . $user->favoriteSongMedia->creator : '' }}" @endif>
                    @if ($user->favoriteSongMedia)
                        @if ($user->favoriteSongMedia->coverimage && filter_var($user->favoriteSongMedia->coverimage, FILTER_VALIDATE_URL))
                            <img src="{{ $user->favoriteSongMedia->coverimage }}"
                                alt="{{ $user->favoriteSongMedia->title }}" class="w-full h-full object-cover">
                        @else
                            <span
                                class="text-gray-600 text-lg md:text-xl text-center px-2">{{ $user->favoriteSongMedia->title }}</span>
                        @endif
                    @else
                        <span class="text-gray-600 text-lg md:text-xl text-center px-2">[fav music]</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- check if profile is private -->
        @if (!$canViewPosts)
            <!-- private account message -->
            <div class="flex flex-col items-center justify-center py-20">
                <div class="text-center">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">This account is private</h2>
                    <p class="text-gray-600">
                        Befriend @<span>{{ $user->username }}</span> to see their posts and reviews
                    </p>
                </div>
            </div>
        @else
            <!-- tabs -->
            <x-tabs :tabs="[
                'posts' => [
                    'title' => 'Posts',
                    'content' => view('components.posts.post-list', [
                        'posts' => $standardPosts,
                        'showAuthor' => false,
                        'postType' => 'standard',
                    ])->render(),
                ],
                'reviews' => [
                    'title' => 'Reviews',
                    'content' => view('components.posts.post-list', [
                        'posts' => $reviewPosts,
                        'showAuthor' => false,
                        'postType' => 'review',
                    ])->render(),
                ],
            ]" />
        @endif

        <x-posts.post-modal />
    </div>
@endsection
