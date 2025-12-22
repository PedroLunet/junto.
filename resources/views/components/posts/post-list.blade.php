@php
    $hasRelevantPosts = false;
    if (!isset($postType) || $postType === 'all') {
        $hasRelevantPosts = count($posts) > 0;
    } else {
        foreach ($posts as $post) {
            $isReview = isset($post->rating) && $post->rating;
            if (($postType === 'review' && $isReview) || ($postType === 'standard' && !$isReview)) {
                $hasRelevantPosts = true;
                break;
            }
        }
    }
@endphp

@if ($hasRelevantPosts)
    <div class="max-w-2xl mx-auto space-y-6">
        @foreach ($posts as $post)
            @php
                $isReview = isset($post->rating) && $post->rating;
            @endphp

            @if (!isset($postType) || $postType === 'all')
                <!-- both types -->
                @if ($isReview)
                    <x-posts.post-review :post="$post" :showAuthor="$showAuthor ?? true" />
                @else
                    <x-posts.post-standard :post="$post" :showAuthor="$showAuthor ?? true" />
                @endif
            @elseif($postType === 'standard' && !$isReview)
                <!-- only standard posts -->
                <x-posts.post-standard :post="$post" :showAuthor="$showAuthor ?? true" />
            @elseif($postType === 'review' && $isReview)
                <!-- only review posts -->
                <x-posts.post-review :post="$post" :showAuthor="$showAuthor ?? true" />
            @endif
        @endforeach
    </div>
@else
    @php
        $emptyMessage = match ($postType ?? 'all') {
            'standard' => 'No standard posts yet',
            'review' => 'No reviews yet',
            default => 'No posts yet',
        };
    @endphp
    <x-ui.empty-state :title="$emptyMessage" icon="fa-camera"
        description="Try changing your filters or create a new post!" />
@endif