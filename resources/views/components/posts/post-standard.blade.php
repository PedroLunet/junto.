<div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8 cursor-pointer"
    onclick="openPostModal({{ json_encode($post) }})">

    @if ($showAuthor ?? true)
        <!-- profile + name -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <!-- user avatar -->
                <div class="w-12 h-12 bg-gray-200 rounded-full"></div>

                <div class="flex flex-col">
                    <span class="font-semibold text-gray-900">
                        {{ $post->author_name }}
                    </span>
                    <span class="text-gray-700 text-lg">
                        @<span>{{ $post->username }}</span>
                    </span>
                </div>
            </div>

            <!-- timestamp -->
            <div class="text-lg text-gray-800 text-right">
                {{ \Carbon\Carbon::parse($post->created_at)->format('H:i') }} <br>
                {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}
            </div>
        </div>
    @endif

    <!-- image -->
    @if ($post->image_url)
        <div class="w-full bg-gray-200 rounded-xl overflow-hidden mb-4">
            <img src="{{ asset('post/' . $post->image_url) }}" onerror="this.src='{{ asset('post/default.jpg') }}'"
                class="w-full h-auto object-cover">
        </div>
    @endif

    <!-- text -->
    @if ($post->content)
        <p class="text-black">
            {{ $post->content }}
        </p>
    @endif

    <!-- interactions -->
    <div class="flex justify-end items-center gap-4 mt-4 text-gray-600">
        <!-- likes -->
        <button onclick="event.stopPropagation(); toggleLike({{ $post->id }})"
            class="bg-transparent border-0 shadow-none p-0 h-auto leading-none flex items-center gap-1 hover:text-red-500 hover:bg-transparent focus:bg-transparent focus:outline-none transition-colors {{ $post->is_liked ?? false ? 'text-red-500 focus:text-red-500' : 'text-gray-600 focus:text-gray-600' }}"
            id="like-btn-{{ $post->id }}">
            <i class="{{ $post->is_liked ?? false ? 'fas' : 'far' }} fa-heart text-2xl"
                id="like-icon-{{ $post->id }}"></i>
            <span class="text-2xl" id="like-count-{{ $post->id }}">{{ $post->likes_count ?? 0 }}</span>
        </button>

        <!-- comments -->
        <div class="flex items-center gap-1">
            <i class="far fa-comment text-2xl"></i>
            <span class="text-2xl">{{ $post->comments_count ?? 0 }}</span>
        </div>
    </div>
</div>
