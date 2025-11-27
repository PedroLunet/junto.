@extends('layouts.app')

@section('content')
    <div class="h-screen flex flex-col overflow-hidden">
        <!-- Fixed Header Section -->
        <div class="shrink-0 px-32 py-10">
            <div class="flex items-start justify-between gap-10 md:gap-12 lg:gap-16 mb-10 md:mb-12">
                @if (Auth::id() === $user->id)
                    <!-- inbox button -->
                    <div class="absolute top-2 right-2">
                        <x-button onclick="window.location='{{ route('friend-requests.index') }}'" variant="secondary"
                            class="p-3">
                            <i class="fas fa-inbox text-2xl"></i>
                        </x-button>
                        @if ($pendingRequestsCount > 0)
                            <div class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-[#F75C03] rounded-full">
                            </div>
                        @endif
                    </div>
                @endif

                <!-- profile header -->
                <div class="flex items-center gap-8 md:gap-10">
                    @auth
                        <div
                            class="w-54 h-54 md:w-60 md:h-60 lg:w-72 lg:h-72 bg-gray-300 rounded-full shrink-0 flex items-center justify-center text-6xl md:text-7xl lg:text-8xl font-bold text-gray-600">
                            <span class="leading-[0.8] mt-[0.1em]">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-2">{{ $user->name }}</h3>
                            <p class="text-2xl md:text-3xl lg:text-4xl text-gray-600 mb-4">@<span>{{ $user->username }}</span>
                            </p>

                            <!-- friends and posts count -->
                            <div class="flex gap-8 md:gap-10 mb-4 md:mb-6">
                                <div>
                                    <a href="{{ url('/friends-' . $user->username) }}" class="hover:underline">
                                        <span class="font-bold text-gray-900 text-xl md:text-2xl">{{ $friendsCount }}</span>
                                        <span class="text-gray-600 text-xl md:text-2xl">Friends</span>
                                    </a>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 text-xl md:text-2xl">{{ $postsCount }}</span>
                                    <span class="text-gray-600 text-xl md:text-2xl">Posts</span>
                                </div>
                            </div>

                            @if (Auth::id() === $user->id)
                                <x-button onclick="openEditProfileModal()" variant="primary"
                                    class="text-xl md:text-2xl font-medium">
                                    Edit Profile
                                </x-button>
                            @else
                                <!-- Friend request button -->
                                <div class="mt-4 md:mt-6">
                                    @include('components.profile.friend-button', [
                                        'user' => $user,
                                        'friendButtonData' => $friendButtonData,
                                    ])
                                </div>
                            @endif
                        </div>
                    @endauth
                </div>

                <!-- 3 favorites -->
                <div class="flex gap-8 md:gap-12 lg:gap-16 justify-end">
                    <!-- fav book -->
                    @if ($user->favoriteBookMedia || Auth::id() === $user->id)
                        <div class="w-36 h-54 md:w-40 md:h-60 lg:w-48 lg:h-72 bg-gray-300 flex items-center justify-center relative"
                            @if ($user->favoriteBookMedia) title="{{ $user->favoriteBookMedia->title }}{{ $user->favoriteBookMedia->creator ? ' - ' . $user->favoriteBookMedia->creator : '' }}" @endif>
                            @if ($user->favoriteBookMedia)
                                @if (Auth::id() === $user->id)
                                    <x-button onclick="removeFavorite('book')" variant="danger"
                                        class="absolute -top-5 -right-5 w-10 h-10 rounded-full flex items-center justify-center text-3xl font-bold z-10 px-0 py-0">-</x-button>
                                @endif
                                @if ($user->favoriteBookMedia->coverimage && filter_var($user->favoriteBookMedia->coverimage, FILTER_VALIDATE_URL))
                                    <img src="{{ $user->favoriteBookMedia->coverimage }}"
                                        alt="{{ $user->favoriteBookMedia->title }}" class="w-full h-full object-cover">
                                @else
                                    <span
                                        class="text-gray-600 text-2xl md:text-xl text-center px-2">{{ $user->favoriteBookMedia->title }}</span>
                                @endif
                            @else
                                <x-button onclick="openAddFavModal('book')" variant="ghost"
                                    class="w-full h-full text-gray-600 text-5xl md:text-6xl lg:text-7xl font-light hover:text-gray-800 hover:bg-gray-400 cursor-pointer px-0 py-0">+</x-button>
                            @endif
                        </div>
                    @endif

                    <!-- fav movie -->
                    @if ($user->favoriteFilmMedia || Auth::id() === $user->id)
                        <div class="w-36 h-54 md:w-40 md:h-60 lg:w-48 lg:h-72 bg-gray-300 flex items-center justify-center relative"
                            @if ($user->favoriteFilmMedia) title="{{ $user->favoriteFilmMedia->title }}{{ $user->favoriteFilmMedia->creator ? ' - ' . $user->favoriteFilmMedia->creator : '' }}" @endif>
                            @if ($user->favoriteFilmMedia)
                                @if (Auth::id() === $user->id)
                                    <x-button onclick="removeFavorite('movie')" variant="danger"
                                        class="absolute -top-5 -right-5 w-10 h-10 rounded-full flex items-center justify-center text-3xl font-bold z-10 px-0 py-0">-</x-button>
                                @endif
                                @if ($user->favoriteFilmMedia->coverimage && filter_var($user->favoriteFilmMedia->coverimage, FILTER_VALIDATE_URL))
                                    <img src="{{ $user->favoriteFilmMedia->coverimage }}"
                                        alt="{{ $user->favoriteFilmMedia->title }}" class="w-full h-full object-cover">
                                @else
                                    <span
                                        class="text-gray-600 text-2xl md:text-xl text-center px-2">{{ $user->favoriteFilmMedia->title }}</span>
                                @endif
                            @else
                                <x-button onclick="openAddFavModal('movie')" variant="ghost"
                                    class="w-full h-full text-gray-600 text-5xl md:text-6xl lg:text-7xl font-light hover:text-gray-800 hover:bg-gray-400 cursor-pointer px-0 py-0">+</x-button>
                            @endif
                        </div>
                    @endif

                    <!-- fav music -->
                    @if ($user->favoriteSongMedia || Auth::id() === $user->id)
                        <div class="w-48 h-56 md:w-56 md:h-56 lg:w-72 lg:h-72 bg-gray-300 flex items-center justify-center relative"
                            @if ($user->favoriteSongMedia) title="{{ $user->favoriteSongMedia->title }}{{ $user->favoriteSongMedia->creator ? ' - ' . $user->favoriteSongMedia->creator : '' }}" @endif>
                            @if ($user->favoriteSongMedia)
                                @if (Auth::id() === $user->id)
                                    <x-button onclick="removeFavorite('music')" variant="danger"
                                        class="absolute -top-5 -right-5 w-10 h-10 rounded-full flex items-center justify-center text-3xl font-bold z-10 px-0 py-0">-</x-button>
                                @endif
                                @if ($user->favoriteSongMedia->coverimage && filter_var($user->favoriteSongMedia->coverimage, FILTER_VALIDATE_URL))
                                    <img src="{{ $user->favoriteSongMedia->coverimage }}"
                                        alt="{{ $user->favoriteSongMedia->title }}" class="w-full h-full object-cover">
                                @else
                                    <span
                                        class="text-gray-600 text-2xl md:text-xl text-center px-2">{{ $user->favoriteSongMedia->title }}</span>
                                @endif
                            @else
                                <x-button onclick="openAddFavModal('music')" variant="ghost"
                                    class="w-full h-full text-gray-600 text-5xl md:text-6xl lg:text-7xl font-light hover:text-gray-800 hover:bg-gray-400 cursor-pointer px-0 py-0">+</x-button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- bio -->
            <div class="mb-10 md:mb-12 px-20 py-2">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">About Me</h2>
                @if ($user->bio)
                    <p class="text-xl md:text-2xl text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                @else
                    @if (Auth::id() === $user->id)
                        <p class="text-xl md:text-2xl text-gray-500 italic">Add a bio to tell others about yourself</p>
                    @else
                        <p class="text-xl md:text-2xl text-gray-500 italic">{{ $user->name }} hasn't added a bio yet</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Scrollable Content Section -->
        <div class="flex-1 overflow-hidden px-32">
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
                <!-- tabs with scrollable content -->
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
        </div>

        <!-- Modals (outside scrollable area) -->
        <x-posts.post-modal />
        @include('components.profile.add-fav-modal')
        @include('components.profile.edit-profile-modal')
        <x-alert />
    </div>

    <script>
        function removeFavorite(type) {
            if (!confirm(`Are you sure you want to remove your favorite ${type}?`)) {
                return;
            }

            fetch('/profile/remove-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // reload the page to show the updated favorites
                        window.location.reload();
                    } else {
                        alert('Error removing favorite: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error removing favorite');
                });
        }

        function toggleLike(postId) {
            if (!window.isAuthenticated) {
                window.location.href = '/login';
                return;
            }

            fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const likeBtn = document.getElementById(`like-btn-${postId}`);
                        const likeCount = document.getElementById(`like-count-${postId}`);
                        const likeIcon = document.getElementById(`like-icon-${postId}`);

                        likeCount.textContent = data.likes_count;

                        if (data.liked) {
                            likeBtn.classList.remove('text-gray-600', 'focus:text-gray-600');
                            likeBtn.classList.add('text-red-500', 'focus:text-red-500');
                            likeIcon.classList.remove('far');
                            likeIcon.classList.add('fas');
                        } else {
                            likeBtn.classList.remove('text-red-500', 'focus:text-red-500');
                            likeBtn.classList.add('text-gray-600', 'focus:text-gray-600');
                            likeIcon.classList.remove('fas');
                            likeIcon.classList.add('far');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
@endsection
