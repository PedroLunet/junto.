@if(count($posts) > 0)
    <div class="max-w-4xl mx-auto space-y-6">
        @foreach($posts as $post)
            @php
                $isReview = isset($post->rating) && $post->rating;
            @endphp
            
            @if(!isset($postType) || $postType === 'all')
                <!-- both types -->
                @if($isReview)
                    <x-post-review :post="$post" :showAuthor="$showAuthor ?? true" />
                @else
                    <x-post-standard :post="$post" :showAuthor="$showAuthor ?? true" />
                @endif
            @elseif($postType === 'standard' && !$isReview)
                <!-- only standard posts -->
                <x-post-standard :post="$post" :showAuthor="$showAuthor ?? true" />
            @elseif($postType === 'review' && $isReview)
                <!-- only review posts -->
                <x-post-review :post="$post" :showAuthor="$showAuthor ?? true" />
            @endif
        @endforeach
    </div>
@else
    <p class="text-gray-500 text-center py-8">No posts yet</p>
@endif