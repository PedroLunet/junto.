@if(count($posts) > 0)
    <div class="max-w-4xl mx-auto space-y-6">
        @foreach($posts as $post)
            <x-post :post="$post" :showAuthor="$showAuthor ?? true" />
        @endforeach
    </div>
@else
    <p class="text-gray-500 text-center py-8">No posts yet</p>
@endif