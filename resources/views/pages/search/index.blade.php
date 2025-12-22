@extends('layouts.app')

@section('content')
    <main class="w-full max-w-4xl mx-auto flex flex-col items-center gap-4 sm:gap-6 px-4 py-4 sm:py-6">
        <div>
            <h1 class="text-lg sm:text-2xl font-black text-center">Search</h1>
            <p class="text-center text-sm sm:text-base">Find what you are looking for!</p>
        </div>

        <div class="w-full flex gap-4 border-b-2 border-gray-200">
            <a href="{{ route('search.users') }}" 
               class="px-4 py-2 {{ request()->route()->getName() === 'search.users' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600' }}">
                Users
            </a>
            <a href="{{ route('search.groups') }}" 
               class="px-4 py-2 {{ request()->route()->getName() === 'search.groups' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600' }}">
                Groups
            </a>
            <a href="{{ route('search.posts') }}" 
               class="px-4 py-2 {{ request()->route()->getName() === 'search.posts' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600' }}">
                Posts
            </a>
            <a href="{{ route('search.comments') }}" 
               class="px-4 py-2 {{ request()->route()->getName() === 'search.comments' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600' }}">
                Comments
            </a>
        </div>

        <form method="get" class="flex flex-col gap-3 sm:gap-6 w-full">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-stretch sm:items-center w-full">
                <input value="{{ old('query', request('query')) }}" type="text" name="query" 
                       placeholder="@if (request()->route()->getName() === 'search.posts') Search posts by content or author...
                       @elseif (request()->route()->getName() === 'search.comments') Search comments by content...
                       @elseif (request()->route()->getName() === 'search.groups') Search groups by name or description...
                       @else Search by name, username, or bio...
                       @endif" 
                       class="flex-1 w-full h-10 border-2 rounded-lg pl-4"  />
                <x-ui.button class="h-10">Search</x-ui.button>
            </div>

            @if (request()->route()->getName() === 'search.users')
                <div class="flex flex-col gap-2 w-full">
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center">
                        <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                        <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Newest first</option>
                            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Oldest first</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center">
                        <label for="join_date_range" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">JOINED</label>
                        <select name="join_date_range" id="join_date_range" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                            <option value="all" {{ request('join_date_range') === 'all' || !request('join_date_range') ? 'selected' : '' }}>All time</option>
                            <option value="last_month" {{ request('join_date_range') === 'last_month' ? 'selected' : '' }}>Last month</option>
                            <option value="last_three_months" {{ request('join_date_range') === 'last_three_months' ? 'selected' : '' }}>Last 3 months</option>
                            <option value="last_year" {{ request('join_date_range') === 'last_year' ? 'selected' : '' }}>Last year</option>
                        </select>
                    </div>
                </div>
            @elseif (request()->route()->getName() === 'search.groups')
                <div class="flex flex-col gap-2 w-full">
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center">
                        <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                        <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="members_desc" {{ request('sort') === 'members_desc' ? 'selected' : '' }}>Most members</option>
                            <option value="members_asc" {{ request('sort') === 'members_asc' ? 'selected' : '' }}>Least members</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center">
                        <label for="min_members" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">MIN MEMBERS</label>
                        <input type="number" name="min_members" id="min_members" min="0" value="{{ request('min_members', 0) }}" class="w-full sm:w-20 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700" onchange="this.form.submit()" />
                    </div>
                </div>
            @elseif (request()->route()->getName() === 'search.posts')
                <div class="flex flex-col gap-2 w-full">
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center">
                        <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                        <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                            <option value="date_desc" {{ request('sort') === 'date_desc' || !request('sort') ? 'selected' : '' }}>Newest first</option>
                            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Oldest first</option>
                        </select>
                    </div>
                </div>
            @elseif (request()->route()->getName() === 'search.comments')
                <div class="flex flex-col gap-2 w-full">
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center">
                        <label for="sort" class="text-xs sm:text-sm font-medium text-gray-600 whitespace-nowrap">SORT BY</label>
                        <select name="sort" id="sort" class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer transition-colors" onchange="this.form.submit()">
                            <option value="date_desc" {{ request('sort') === 'date_desc' || !request('sort') ? 'selected' : '' }}>Newest first</option>
                            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Oldest first</option>
                        </select>
                    </div>
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
            @elseif (request()->route()->getName() === 'search.groups')
                <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($groups as $group)
                        <a href="{{ route('groups.show', $group) }}" class="flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer block">

                            <div class="h-24 bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 relative">
                                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                            </div>

                            <div class="px-6 pb-6 flex-1 flex flex-col relative">

                                <div class="-mt-12 mb-4">
                                    <div class="bg-white p-2 rounded-2xl shadow-sm inline-block">
                                        <div class="h-20 w-20 bg-[#820263] rounded-xl flex items-center justify-center text-white text-3xl font-extrabold shadow-inner">
                                            {{ substr($group->name, 0, 1) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h2 class="text-2xl font-black text-gray-900 leading-tight mb-3 group-hover:text-[#820263] transition-colors">
                                        {{ $group->name }}
                                    </h2>
                                    
                                    <div class="flex items-center gap-3">
                                        @if($group->isprivate)
                                            <span class="inline-flex items-center bg-amber-50 text-amber-700 text-sm px-3 py-1 rounded-full font-bold border border-amber-200">
                                                <i class="fas fa-lock mr-1.5"></i> Private
                                            </span>
                                        @else
                                            <span class="inline-flex items-center bg-green-50 text-green-700 text-sm px-3 py-1 rounded-full font-bold border border-green-200">
                                                <i class="fas fa-globe mr-1.5"></i> Public
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-gray-700 mb-6 text-base leading-relaxed line-clamp-3 flex-1">
                                    {{ $group->description }}
                                </p>

                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center text-gray-500 text-sm font-medium">
                                        <i class="fas fa-users mr-2 text-gray-400"></i>
                                        {{ $group->members_count }} Members
                                    </div>
                                    
                                    <span class="text-[#820263] font-bold text-sm group-hover:underline flex items-center">
                                        View Group <i class="fas fa-arrow-right ml-2 text-sm transition-transform group-hover:translate-x-1"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-24 text-center">
                            <p>No groups found.</p>
                        </div>
                    @endforelse
                </div>
            @elseif (request()->route()->getName() === 'search.posts')
                @forelse ($posts as $post)
                    @if ($post->post_type === 'review')
                        <x-posts.post-review :post="$post" :showAuthor="true" />
                    @else
                        <x-posts.post-standard :post="$post" :showAuthor="true" />
                    @endif
                @empty
                    <p>No posts found.</p>
                @endforelse
            @elseif (request()->route()->getName() === 'search.comments')
                @forelse ($comments as $comment)
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 cursor-pointer hover:shadow-lg transition-shadow"
                        onclick="openCommentPost({{ $comment->postid }})">
                        
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('profile/default.png') }}" alt="User Avatar"
                                    class="w-10 h-10 rounded-full object-cover bg-gray-200">
                                
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-900 text-sm">{{ $comment->author_name }}</span>
                                    <span class="text-gray-500 text-xs">@<span class="text-gray-500">{{ $comment->username }}</span></span>
                                </div>
                            </div>
                            
                            <div class="text-xs text-gray-500 text-right">
                                {{ \Carbon\Carbon::parse($comment->created_at)->format('H:i') }} <br>
                                {{ \Carbon\Carbon::parse($comment->created_at)->format('d/m/Y') }}
                            </div>
                        </div>

                        <p class="text-gray-700 text-sm mb-3">{{ $comment->content }}</p>
                        
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-200 text-xs text-gray-500">
                            <i class="fas fa-reply text-gray-400"></i>
                            <span>Posted on <strong>{{ $comment->post_author_name }}'s</strong> post</span>
                        </div>
                    </div>
                @empty
                    <p>No comments found.</p>
                @endforelse
            @endif
        </div>
    </main>

    @if (request()->route()->getName() === 'search.posts' || request()->route()->getName() === 'search.comments')
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

            function openCommentPost(postId) {
                fetch(`/posts/${postId}/view`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    openPostModal(data.post);
                })
                .catch(error => {
                    console.error('Error loading post:', error);
                });
            }
        </script>
    @endif
@endsection
