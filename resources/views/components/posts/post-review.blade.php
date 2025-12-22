<div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 {{ $isViewOnly ?? false ? '' : 'cursor-pointer' }}"
    @if (!($isViewOnly ?? false)) onclick="openPostModal({{ json_encode($post) }})" @endif>

    <!-- profile + name -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <!-- user avatar -->
            <img src="{{ asset('profile/default.png') }}" alt="User Avatar"
                class="w-10 h-10 rounded-full object-cover bg-gray-200">

            <div class="flex flex-col">
                <span class="font-semibold text-gray-900 text-sm">
                    {{ $post->author_name ?? ($post->user->name ?? 'Unknown User') }}
                </span>
                <span class="text-gray-500 text-xs">
                    @<span>{{ $post->username ?? ($post->user->username ?? 'unknown') }}</span>
                </span>
            </div>
        </div>

        <!-- timestamp -->
        <div class="text-xs text-gray-500 text-right">
            {{ \Carbon\Carbon::parse($post->created_at ?? $post->createdat)->format('H:i') }} <br>
            {{ \Carbon\Carbon::parse($post->created_at ?? $post->createdat)->format('d/m/Y') }}
        </div>
    </div>

    <!-- REVIEWS -->
    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        <!-- cover -->
        <div class="shrink-0 flex sm:block justify-center">
            @if ($post->media_poster ?? ($post->review->media->coverimage ?? false))
                <img src="{{ $post->media_poster ?? $post->review->media->coverimage }}"
                    class="rounded-lg shadow-sm object-cover {{ ($post->media_type ?? ($post->review->media->type ?? 'movie')) === 'music' ? 'w-32 h-32 sm:w-24 sm:h-24' : 'w-32 h-48 sm:w-24 sm:h-36' }}"
                    alt="{{ $post->media_title ?? ($post->review->media->title ?? 'Media') }}">
            @else
                <div
                    class="bg-gray-300 rounded-lg shadow-sm flex items-center justify-center {{ ($post->media_type ?? ($post->review->media->type ?? 'movie')) === 'music' ? 'w-32 h-32 sm:w-24 sm:h-24' : 'w-32 h-48 sm:w-24 sm:h-36' }}">
                    <span
                        class="text-gray-600 text-center px-2 text-xs">{{ $post->media_title ?? ($post->review->media->title ?? 'No Image') }}</span>
                </div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start gap-2 mb-1">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 break-words">
                    {{ $post->media_title ?? ($post->review->media->title ?? 'Untitled') }}</h3>
                <div class="flex gap-0.5 text-yellow-400 text-sm sm:text-base shrink-0 pt-1">
                    @for ($i = 0; $i < ($post->rating ?? ($post->review->rating ?? 0)); $i++)
                        <i class="fas fa-star"></i>
                    @endfor
                </div>
            </div>
            <p class="text-sm sm:text-base text-gray-700 font-medium mb-0.5">
                {{ $post->media_creator ?? ($post->review->media->creator ?? '') }}</p>
            <p class="text-xs sm:text-sm text-gray-700 mb-3">{{ $post->media_year ?? ($post->review->media->releaseyear ?? '') }}
            </p>

            <p class="text-sm sm:text-base text-black font-light break-words">
                {!! \App\Helpers\MentionHelper::convertMentionsToLinks($post->content ?? ($post->review->content ?? ''), $post->tagged_users ?? []) !!}
            </p>
        </div>
    </div>

    <!-- interactions -->
    <div class="flex justify-end items-center gap-4 mt-4 text-gray-600">
        <!-- likes -->
        @if ($isViewOnly ?? false)
            <div class="flex items-center gap-1">
                <i class="far fa-heart text-lg"></i>
                <span class="text-lg">{{ $post->likes_count ?? 0 }}</span>
            </div>
        @else
            <button onclick="event.stopPropagation(); toggleLike({{ $post->id }})"
                class="bg-transparent border-0 shadow-none p-0 h-auto leading-none flex items-center gap-1 hover:text-red-500 hover:bg-transparent focus:bg-transparent focus:outline-none focus:ring-2 focus:ring-red-500 focus:rounded-sm transition-colors {{ $post->is_liked ?? false ? 'text-red-500 focus:text-red-500' : 'text-gray-600 focus:text-gray-600' }}"
                id="like-btn-{{ $post->id }}">
                <i class="{{ $post->is_liked ?? false ? 'fas' : 'far' }} fa-heart text-lg"
                    id="like-icon-{{ $post->id }}"></i>
                <span class="text-lg" id="like-count-{{ $post->id }}">{{ $post->likes_count ?? 0 }}</span>
            </button>
        @endif

        <!-- comments -->
        <div class="flex items-center gap-1">
            <i class="far fa-comment text-lg"></i>
            <span class="text-lg">{{ $post->comments_count ?? 0 }}</span>
        </div>
    </div>
</div>
