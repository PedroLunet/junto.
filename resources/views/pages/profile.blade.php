@extends('layouts.app')

@section('content')
    <div class="flex flex-col h-full -mx-6 -mt-6 overflow-y-auto">
        <!-- Header Section -->
        <div class="px-16 pt-10 pb-6">
            <div class="flex items-start justify-between gap-6 md:gap-8 lg:gap-10 mb-6 md:mb-8">
                <!-- profile header -->
                <div class="flex items-center gap-4 md:gap-6">
                    <div
                        class="w-32 h-32 md:w-36 md:h-36 lg:w-40 lg:h-40 rounded-full shrink-0 overflow-hidden relative bg-gray-300 border-2 border-gray-200 flex items-center justify-center">
                        <img src="{{ $user->profilepicture ? asset('profile/' . $user->profilepicture) : asset('profile/default.png') }}"
                            alt="Profile Picture" class="absolute inset-0 w-full h-full object-cover"
                            onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
                    </div>

                    <div class="flex-1">
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                        <p class="text-lg md:text-xl text-gray-600 mb-3">@<span>{{ $user->username }}</span>
                        </p>

                        <!-- friends and posts count -->
                        <div class="flex gap-5 md:gap-6 mb-3 md:mb-4">
                            <div>
                                <a href="{{ url('/friends-' . $user->username) }}" class="hover:underline">
                                    <span class="font-bold text-gray-900 text-lg">{{ $friendsCount }}</span>
                                    <span class="text-gray-600 text-lg">Friends</span>
                                </a>
                            </div>
                            <div>
                                <span class="font-bold text-gray-900 text-lg">{{ $postsCount }}</span>
                                <span class="text-gray-600 text-lg">Posts</span>
                            </div>
                        </div>

                        @auth
                            @if (Auth::id() === $user->id)
                                <a href="{{ route('profile.edit') }}">
                                    <x-ui.button variant="primary" class="text-base font-medium">
                                        Edit Profile
                                    </x-ui.button>
                                </a>
                            @else
                                <!-- Friend request button -->
                                <div class="mt-3 md:mt-4 flex gap-3">
                                    <x-profile.friend-button :user="$user" :friendButtonData="$friendButtonData" />

                                    @if(Auth::user()->isFriendsWith($user->id))
                                        <a href="{{ route('messages.show', $user->id) }}">
                                            <x-ui.button variant="secondary" class="px-4 py-2 inline-flex items-center">
                                                <i class="fa-solid fa-envelope mr-2"></i>
                                                Message
                                            </x-ui.button>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @else
                            <!-- Befriend button for guests -->
                            <div class="mt-3 md:mt-4">
                                <x-ui.button variant="primary" onclick="window.location.href='/login'"
                                    class="text-base font-medium">
                                    Befriend
                                </x-ui.button>
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- 3 favorites -->
                <div class="flex gap-4 md:gap-6 lg:gap-8 justify-end">
                    <x-profile.fav :user="$user" :media="$user->favoriteBookMedia" type="book" />
                    <x-profile.fav :user="$user" :media="$user->favoriteFilmMedia" type="movie" />
                    <x-profile.fav :user="$user" :media="$user->favoriteSongMedia" type="music" />
                </div>
            </div>

            <!-- bio -->
            <div class="mb-6 md:mb-8 px-10 py-2">
                <h2 class="text-xl font-bold text-gray-900 mb-2">About Me</h2>
                @if ($user->bio)
                    <p class="text-base md:text-lg text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                @else
                    @if (Auth::id() === $user->id)
                        <p class="text-base md:text-lg text-gray-500 italic">Add a bio to tell others about yourself</p>
                    @else
                        <p class="text-base md:text-lg text-gray-500 italic">{{ $user->name }} hasn't added a bio yet</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Content Section -->
        <div class="px-16 pb-6">
            <!-- Only show private message if profile is actually private -->
            @if (!$canViewPosts)
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">This account is private</h2>
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

        <x-posts.post-modal />
        <x-profile.add-fav-modal />
        <x-ui.confirm />
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
