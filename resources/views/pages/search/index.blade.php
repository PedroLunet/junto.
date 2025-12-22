@extends('layouts.app')

@section('content')
    <main class="w-full max-w-4xl mx-auto flex flex-col items-center gap-4 sm:gap-6 px-4 py-4 sm:py-6">
        <div>
            <h1 class="text-lg sm:text-2xl font-black text-center">Search</h1>
            <p class="text-center text-sm sm:text-base">Find users or posts!</p>
        </div>

        <div class="w-full flex gap-4 border-b-2 border-gray-200">
            <a href="{{ route('search.users') }}" 
               class="px-4 py-2 {{ request()->route()->getName() === 'search.users' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600' }}">
                Users
            </a>
            <a href="{{ route('search.posts') }}" 
               class="px-4 py-2 {{ request()->route()->getName() === 'search.posts' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600' }}">
                Posts
            </a>
        </div>

        <form method="get" class="flex flex-col gap-3 sm:gap-6 w-full">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-stretch sm:items-center w-full">
                <input value="{{ old('query', request('query')) }}" type="text" name="query" 
                       placeholder="{{ request()->route()->getName() === 'search.posts' ? 'Search posts by content or author...' : 'Search by name, username, or bio...' }}" 
                       class="flex-1 w-full h-10 border-2 rounded-lg pl-4"  />
                <x-ui.button class="h-10">Search</x-ui.button>
            </div>

            @if (request()->route()->getName() === 'search.users')
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center w-full">
                    <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                    <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Newest first</option>
                        <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Oldest first</option>
                    </select>
                </div>
            @else
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center w-full">
                    <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                    <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                        <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Newest first</option>
                        <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Oldest first</option>
                    </select>
                </div>
            @endif
        </form>

        <div class="w-full flex flex-col gap-3">
            @if (request()->route()->getName() === 'search.users')
                @forelse ($users as $user)
                    @php
                        $isLoggedIn = Auth::check();
                        $isCurrentUser = $isLoggedIn && $user->id == Auth::id();
                        $isFriend = in_array($user->id, $friends);
                        $friendButtonData = !$isFriend && $isLoggedIn ? $friendService->getFriendButtonData($user) : null;
                    @endphp

                    @if (!$isCurrentUser)
                        <x-ui.user-card :user="$user" :showUnfriendButton="$isLoggedIn && $isFriend" :showBefriendButton="$isLoggedIn && !$isFriend" :friendButtonData="$friendButtonData"
                            :unfriendRoute="$isLoggedIn ? route('friends.unfriend', $user->id) : route('login')" :confirmMessage="$isLoggedIn ? 'Are you sure you want to unfriend ' . $user->name . '?' : ''" />
                    @endif

                @empty
                    <p>No users found.</p>
                @endforelse
            @else
                @forelse ($posts as $post)
                    @if ($post->post_type === 'review')
                        <x-posts.post-review :post="$post" :showAuthor="true" />
                    @else
                        <x-posts.post-standard :post="$post" :showAuthor="true" />
                    @endif
                @empty
                    <p>No posts found.</p>
                @endforelse
            @endif
        </div>
    </main>

    @if (request()->route()->getName() === 'search.posts')
        <x-posts.post-modal />
        <x-posts.edit.edit-regular-modal />
        <x-posts.edit.edit-review-modal />

        @yield('modal-overlay')

        <script>
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
                            const likeIcon = document.getElementById(`like-icon-${postId}`);
                            const likeCount = document.getElementById(`like-count-${postId}`);

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
    @endif
@endsection
