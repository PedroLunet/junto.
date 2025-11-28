@extends('layouts.app')

@section('content')
    <div class="flex flex-col min-h-0 h-full -m-6">
        <!-- Fixed Header Section -->
        <div class="shrink-0 px-32 pt-20 pb-10">
            <div class="flex items-start justify-between gap-10 md:gap-12 lg:gap-16 mb-10 md:mb-12">
                <!-- profile header -->
                <div class="flex items-center gap-8 md:gap-10">
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

                        @auth
                            @if (Auth::id() === $user->id)
                                <x-ui.button onclick="openEditProfileModal()" variant="primary"
                                    class="text-xl md:text-2xl font-medium">
                                    Edit Profile
                                    </x-button>
                                @else
                                    <!-- Friend request button -->
                                    <div class="mt-4 md:mt-6">
                                        <x-profile.friend-button :user="$user" :friendButtonData="$friendButtonData" />
                                    </div>
                            @endif
                        @else
                            <!-- Befriend button for guests -->
                            <div class="mt-4 md:mt-6">
                                <x-ui.button variant="primary" onclick="window.location.href='/login'"
                                    class="text-xl md:text-2xl font-medium">
                                    Befriend
                                    </x-button>
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- 3 favorites -->
                <div class="flex gap-8 md:gap-12 lg:gap-16 justify-end">
                    <x-profile.fav :user="$user" :media="$user->favoriteBookMedia" type="book" />
                    <x-profile.fav :user="$user" :media="$user->favoriteFilmMedia" type="movie" />
                    <x-profile.fav :user="$user" :media="$user->favoriteSongMedia" type="music" />
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
            <!-- Only show private message if profile is actually private -->
            @if (!$canViewPosts)
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
                <x-ui.tabs :tabs="[
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
        <x-profile.add-fav-modal />
        <x-profile.edit-profile-modal />
        <x-ui.alert />
    </div>

    <script>
        function removeFavorite(type) {
            window.showAlert({
                title: 'Remove Favorite',
                message: `Are you sure you want to remove your favorite ${type}?`,
                confirmText: 'Remove',
                cancelText: 'Cancel',
                showCancel: true,
                type: 'danger',
                onConfirm: function() {
                    fetch('/profile/remove-favorite', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                type: type
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                window.showAlert({
                                    title: 'Error',
                                    message: 'Error removing favorite: ' + (data.message ||
                                        'Unknown error'),
                                    type: 'danger'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            window.showAlert({
                                title: 'Error',
                                message: 'Error removing favorite',
                                type: 'danger'
                            });
                        });
                }
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
