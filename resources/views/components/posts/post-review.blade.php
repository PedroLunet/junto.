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
                        {{ $post->author_name ?? ($post->user->name ?? 'Unknown User') }}
                    </span>
                    <span class="text-gray-700 text-lg">
                        @<span>{{ $post->username ?? ($post->user->username ?? 'unknown') }}</span>
                    </span>
                </div>
            </div>

            <!-- timestamp -->
            <div class="text-lg text-gray-800 text-right">
                {{ \Carbon\Carbon::parse($post->created_at ?? $post->createdat)->format('H:i') }} <br>
                {{ \Carbon\Carbon::parse($post->created_at ?? $post->createdat)->format('d/m/Y') }}
            </div>
        </div>
    @endif

    <!-- REVIEWS -->
    <div class="flex gap-4 mb-4">
        <!-- cover -->
        <div class="shrink-0">
            @if ($post->media_poster ?? ($post->review->media->coverimage ?? false))
                <img src="{{ $post->media_poster ?? $post->review->media->coverimage }}"
                    class="rounded-lg shadow-sm object-cover {{ ($post->media_type ?? ($post->review->media->type ?? 'movie')) === 'music' ? 'w-40 h-40' : 'w-40 h-64' }}"
                    alt="{{ $post->media_title ?? ($post->review->media->title ?? 'Media') }}">
            @else
                <div
                    class="bg-gray-300 rounded-lg shadow-sm flex items-center justify-center {{ ($post->media_type ?? ($post->review->media->type ?? 'movie')) === 'music' ? 'w-40 h-40' : 'w-40 h-64' }}">
                    <span
                        class="text-gray-600 text-center px-2">{{ $post->media_title ?? ($post->review->media->title ?? 'No Image') }}</span>
                </div>
            @endif
        </div>

        <div class="flex-1">
            <div class="flex justify-between items-start gap-4 mb-1">
                <h3 class="text-4xl font-bold text-gray-900">
                    {{ $post->media_title ?? ($post->review->media->title ?? 'Untitled') }}</h3>
                <div class="flex gap-0.5 text-yellow-400 text-2xl shrink-0 pt-1">
                    @for ($i = 0; $i < ($post->rating ?? ($post->review->rating ?? 0)); $i++)
                        <i class="fas fa-star"></i>
                    @endfor
                </div>
            </div>
            <p class="text-xl text-gray-700 font-medium mb-0.5">
                {{ $post->media_creator ?? ($post->review->media->creator ?? '') }}</p>
            <p class="text-lg text-gray-700 mb-3">{{ $post->media_year ?? ($post->review->media->releaseyear ?? '') }}
            </p>

            <p class="text-black font-light">
                {{ $post->content ?? ($post->review->content ?? '') }}
            </p>
        </div>
    </div>

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
