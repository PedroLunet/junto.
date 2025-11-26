@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto space-y-6">
            @foreach($posts as $post)
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8 cursor-pointer"
                    onclick="openPostModal({{ json_encode($post) }})">

                    <!-- profile + name -->
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <!-- provisorio antes de ter foto -->
                            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>

                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-900">
                                    {{ $post->author_name }}
                                </span>
                                <span class="text-gray-700 text-lg">
                                    @<span>{{$post->username}}</span>
                                </span>
                            </div>
                        </div>

                        <!-- timestamp -->
                        <div class="text-lg text-gray-800">
                            {{ \Carbon\Carbon::parse($post->created_at)->format('H:i') }} <br>
                            {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}
                        </div>
                    </div>


                    <!-- REVIEWS!!!!! -->
                    @if($post->post_type === 'review')
                        <div class="flex gap-4 mb-4">
                            <!-- cover -->
                            <div class="shrink-0">
                                <img src="{{ $post->media_poster }}" 
                                     class="rounded-lg shadow-sm object-cover {{ $post->media_type === 'music' ? 'w-40 h-40' : 'w-40 h-64' }}" 
                                     alt="{{ $post->media_title }}">
                            </div>
                            
                          
                            <div class="flex-1 relative">
                                 <!-- stars -->
                                 <div class="absolute top-0 right-0 text-yellow-400 text-2xl">
                                    @for($i = 0; $i < $post->rating; $i++)
                                        <i class="fas fa-star"></i>
                                    @endfor
                                 </div>
                    
                                 <h3 class="text-4xl font-bold text-gray-900 pr-24 mb-1">{{ $post->media_title }}</h3>
                                 <p class="text-xl text-gray-700 font-medium mb-0.5">{{ $post->media_creator }}</p>
                                 <p class="text-lg text-gray-700 mb-3">{{ $post->media_year }}</p>
                                 
                                 <p class="text-black font-light">
                                    {{ $post->content }}
                                 </p>
                            </div>
                        </div>


                    @else
                        <!-- image -->
                        @if($post->image_url)
                            <div class="w-full bg-gray-200 rounded-xl overflow-hidden mb-4">
                                <img src="{{ asset('storage/' . $post->image_url) }}" class="w-full h-auto object-cover">
                            </div>
                        @endif

                        <!-- text -->
                        <p class="text-black">
                            {{ $post->content }}
                        </p>
                    @endif


                    <!-- interactions -->
                    <div class="flex justify-end items-center gap-4 mt-4 text-gray-600">

                        <!-- likes -->
                        <button 
                            onclick="event.stopPropagation(); toggleLike({{ $post->id }})" 
                            class="bg-transparent border-0 shadow-none p-0 h-auto leading-none flex items-center gap-1 hover:text-red-500 hover:bg-transparent focus:bg-transparent focus:outline-none transition-colors {{ $post->is_liked ? 'text-red-500 focus:text-red-500' : 'text-gray-600 focus:text-gray-600' }}"
                            id="like-btn-{{ $post->id }}"
                        >
                            <span class="text-2xl" id="like-count-{{ $post->id }}">{{ $post->likes_count ?? 0 }}</span>
                            <i class="{{ $post->is_liked ? 'fas' : 'far' }} fa-heart text-2xl" id="like-icon-{{ $post->id }}"></i>
                        </button>

                        <!-- comments -->
                        <div class="flex items-center gap-1">
                            <span class="text-2xl">{{ $post->comments_count ?? 0 }}</span>
                            <i class="far fa-comment text-2xl"></i>
                        </div>
                    </div>

                </div>

            @endforeach
        </div>
    </div>

    <x-post-modal />
    <x-edit-regular-modal />
    <x-edit-review-modal />

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