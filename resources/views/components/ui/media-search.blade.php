@props([
    'type' => 'media', // music, book, movie
    'searchId' => 'mediaSearch',
    'searchResultsId' => 'mediaSearchResults',
    'selectedId' => 'selectedMedia',
    'inputName' => 'media_id',
    'cover' => null,
    'title' => '',
    'creator' => '',
    'year' => '',
    'removeBtnId' => 'removeMediaBtn',
    'removeBtn' => true,
    'searchPlaceholder' => 'Search...',
    'label' => 'What media did you select?',
])

<div class="mb-6">
    <label class="block font-medium text-gray-700 mb-2">{{ $label }}</label>
    <div class="relative" id="{{ $type === 'music' ? 'musicSearchContainer' : $searchId . 'Container' }}">
        <input type="text" id="{{ $searchId }}" placeholder="{{ $searchPlaceholder }}"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
            autocomplete="off">
        <div id="{{ $searchResultsId }}"
            class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1">
        </div>
    </div>
</div>
