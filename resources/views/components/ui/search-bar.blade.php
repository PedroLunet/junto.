@props(['id' => 'search-input', 'placeholder' => 'Search...', 'width' => 'w-80'])

<div class="relative w-full sm:{{ $width }} max-w-full sm:max-w-none">
    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
        <i class="fas fa-search text-gray-400 text-base"></i>
    </div>
    <input type="text" id="{{ $id }}" placeholder="{{ $placeholder }}"
        class="pl-10 pr-8 py-3 text-base text-gray-800 border border-gray-300 rounded-lg outline-none transition-all duration-200 placeholder:text-gray-400 focus:border-[#820273] focus:ring-4 focus:ring-purple-100 w-full"
        {{ $attributes }}>
</div>
