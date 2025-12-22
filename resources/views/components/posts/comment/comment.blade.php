<div class="flex gap-3 p-3 rounded-xl {{ $isViewOnly ?? false ? 'border border-gray-100 bg-gray-50' : 'group hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-gray-100' }}"
    data-comment-id="{{ $comment->id }}">
    <img src="{{ $comment->author_picture ? asset('profile/' . $comment->author_picture) : asset('profile/default-avatar.png') }}"
        alt="{{ $comment->author_name ?? 'User' }}" class="w-10 h-10 rounded-full shrink-0 mt-1 object-cover"
        onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}'">
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline justify-between mb-1">
            <span class="font-semibold text-gray-900 truncate">{{ $comment->author_name ?? '' }}</span>
            <div class="flex items-center gap-2">
                <span
                    class="text-xs text-gray-500 ml-2 shrink-0">{{ isset($comment->created_at) ? \Carbon\Carbon::parse($comment->created_at)->format('d/m/Y H:i') : '' }}</span>
                @auth
                    @if (isset($comment->user_id) && $comment->user_id == auth()->id())
                        <button onclick="toggleEditComment({{ $comment->id }})"
                            class="edit-btn text-gray-500 hover:text-blue-600 transition-colors" title="Edit comment">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteComment({{ $comment->id }})"
                            class="delete-btn text-gray-500 hover:text-red-600 transition-colors" title="Delete comment">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    @endif
                @endauth
            </div>
        </div>
        <p class="comment-text text-gray-700 text leading-relaxed wrap-break-word">{{ $comment->content ?? '' }}</p>
        <div class="comment-edit-form hidden mt-2">
            <textarea
                class="edit-textarea w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                rows="2">{{ $comment->content ?? '' }}</textarea>
            <div class="flex gap-2 mt-2">
                <button onclick="saveCommentEdit({{ $comment->id }})"
                    class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    Save
                </button>
                <button onclick="cancelCommentEdit({{ $comment->id }})"
                    class="px-3 py-1 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors text-sm">
                    Cancel
                </button>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-2">
            @auth
                <button onclick="event.stopPropagation(); toggleCommentLike({{ $comment->id }})"
                    class="flex items-center gap-1 text-xs hover:text-red-500 transition-colors {{ $comment->is_liked ?? false ? 'text-red-500' : 'text-gray-500' }}"
                    id="comment-like-btn-{{ $comment->id }}">
                    <i class="{{ $comment->is_liked ?? false ? 'fas' : 'far' }} fa-heart"
                        id="comment-like-icon-{{ $comment->id }}"></i>
                    <span id="comment-like-count-{{ $comment->id }}">{{ $comment->likes_count ?? 0 }}</span>
                </button>
            @else
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <i class="far fa-heart"></i>
                    <span>{{ $comment->likes_count ?? 0 }}</span>
                </div>
            @endauth
        </div>
    </div>
</div>
