@extends('layouts.app')

@section('title', $pageTitle ?? 'Home')

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (isset($pageTitle) && $pageTitle === 'Friends Feed' && empty($posts))
            <div class="p-6 rounded text-center">
                <p class="text-gray-600">You don't have any friends yet. Start by sending some friend requests!</p>
                <x-ui.button onclick="window.location='{{ route('search.users') }}'" variant="primary" class="mt-4 px-6 py-2">
                    Find Friends
                    </x-button>
            </div>
        @else
            <div class="max-w-4xl mx-auto space-y-6">
                @foreach ($posts as $post)
                    @if ($post->post_type === 'review')
                        <x-posts.post-review :post="$post" :showAuthor="true" />
                    @else
                        <x-posts.post-standard :post="$post" :showAuthor="true" />
                    @endif
                @endforeach
            </div>
        @endif
    </div>

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
