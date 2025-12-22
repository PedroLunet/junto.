<div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 {{ $isViewOnly ?? false ? '' : 'cursor-pointer' }}"
    @if (!($isViewOnly ?? false)) onclick="openPostModal({{ json_encode($post) }})" @endif>

    <!-- profile + name -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <!-- user avatar -->
            <!-- user avatar -->
            @if ($post->author_image ?? false)
                <img src="{{ asset('profile/' . $post->author_image) }}" alt="User Avatar"
                    class="w-10 h-10 rounded-full object-cover bg-gray-200"
                    onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
            @else
                <img src="{{ asset('profile/default.png') }}" alt="User Avatar"
                    class="w-10 h-10 rounded-full object-cover bg-gray-200">
            @endif

            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-900 text-sm">
                        {{ $post->author_name }}
                    </span>
                    @if (!empty($post->group_name))
                        <span class="text-gray-600 text-xs">
                            in <a href="{{ route('groups.show', $post->groupid) }}" onclick="event.stopPropagation()"
                                class="font-medium text-[#38157a] hover:underline">{{ $post->group_name }}</a>
                        </span>
                    @endif
                </div>
                <span class="text-gray-500 text-xs">
                    @<span>{{ $post->username }}</span>
                </span>
            </div>
        </div>

        <!-- timestamp -->
        <div class="text-xs text-gray-600 text-right">
            {{ \Carbon\Carbon::parse($post->created_at)->format('H:i') }} <br>
            {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}
        </div>
    </div>

    <!-- image -->
    @if ($post->image_url)
        <div class="w-full bg-gray-200 rounded-xl overflow-hidden mb-4">
            <img src="{{ asset('post/' . $post->image_url) }}" onerror="this.src='{{ asset('post/default.jpg') }}'"
                class="w-full max-h-96 object-cover" alt="Post Image">
        </div>
    @endif

    <!-- text -->
    @if ($post->content)
        <p class="text-black">
            {!! \App\Helpers\MentionHelper::convertMentionsToLinks(preg_replace('/^GROUP POST:\s*/i', '', $post->content), $post->tagged_users ?? []) !!}
        </p>
    @endif

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
            <span class="text-lg comments-count"
                id="comment-count-{{ $post->id }}">{{ $post->comments_count ?? 0 }}</span>
        </div>
    </div>
</div>