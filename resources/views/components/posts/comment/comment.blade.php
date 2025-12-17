<div
    class="flex gap-3 p-3 rounded-xl {{ $isViewOnly ?? false ? 'border border-gray-100 bg-gray-50' : 'group hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-gray-100' }}">
    <div class="w-10 h-10 bg-gray-200 rounded-full shrink-0 mt-1"></div>
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline justify-between mb-1">
            <span class="font-semibold text-gray-900 truncate">{{ $comment->author_name ?? '' }}</span>
            <span
                class="text-lg text-gray-600 ml-2 shrink-0">{{ isset($comment->created_at) ? \Carbon\Carbon::parse($comment->created_at)->format('d/m/Y') : '' }}</span>
        </div>
        <p class="text-gray-700 text leading-relaxed wrap-break-word">{{ $comment->content ?? '' }}</p>
    </div>
</div>
