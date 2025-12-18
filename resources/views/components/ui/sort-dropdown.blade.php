@props(['options', 'defaultValue' => null, 'onSort' => 'sortReports', 'onToggleOrder' => 'toggleSortOrder'])

<div class="inline-flex items-center gap-2">
    <label class="text-xs sm:text-sm text-gray-600 mr-2 whitespace-nowrap">SORT BY</label>
    <div class="relative">
        <select id="sort-select" onchange="{{ $onSort }}(this.value)"
            class="appearance-none bg-white border border-gray-300 rounded-lg px-3 sm:px-4 py-1.5 sm:py-2 pr-8 sm:pr-10 text-xs sm:text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer">
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" {{ $defaultValue === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 flex items-center text-gray-700">
            <svg class="fill-current h-3 w-3 sm:h-4 sm:w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
            </svg>
        </div>
    </div>
    <button id="sort-order-btn" onclick="{{ $onToggleOrder }}()"
        class="bg-white border border-gray-300 rounded-lg p-1.5 sm:p-2 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
        <i id="sort-order-icon" class="fas fa-arrow-down text-gray-700 text-sm sm:text-base"></i>
    </button>
</div>
