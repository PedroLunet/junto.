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

                        <!-- friends and posts count -->
                        <div class="flex gap-6 mb-3">
                            <div>
                                <span class="font-bold text-gray-900">{{ $friendsCount }}</span>
                                <span class="text-gray-600 ml-1">Friends</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-900">{{ $postsCount }}</span>
                                <span class="text-gray-600 ml-1">Posts</span>
                            </div>
                        </div>

                        @if (Auth::id() === $user->id)
                            <p class="text-xl italic text-gray-500">This is your profile</p>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- 3 favorites -->
            <div class="flex gap-8 mr-40">
                <!-- fav music -->
                @if ($user->favoriteSongMedia)
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

        <!-- check if profile is private -->
        @if ($user->isprivate && !$isFriend && Auth::id() !== $user->id)
            <!-- private account message -->
            <div class="flex flex-col items-center justify-center py-20">
                <div class="text-center">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">This account is private</h2>
                    <p class="text-gray-600">Befriend @<span>{{ $user->username }}</span> to see their posts and reviews</p>
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
