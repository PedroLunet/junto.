<div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 cursor-pointer"
     onclick="openPostModal({{ json_encode($post) }})">
    
    <!-- author info -->
    @if($showAuthor ?? true)
        <div class="flex items-center mb-3">
            <div class="font-semibold text-gray-900">{{ $post->author_name }}</div>
            <div class="text-gray-500 text-base ml-2">@ {{ $post->username }}</div>
        </div>
    @endif

    <!-- post content -->
    @if($post->content)
        <div class="text-gray-800 leading-relaxed">
            {{ $post->content }}
        </div>
    @endif

    <!-- post image -->
    @if($post->image_url)
        <div class="mt-4">
            <img src="{{ asset('post/' . $post->image_url) }}" 
                 onerror="this.src='{{ asset('post/default.jpg') }}'"
                 alt="image"
                 class="w-full max-w-md rounded-lg shadow-sm border border-gray-200 mx-auto">
        </div>
    @endif
</div>