@props(['filters', 'activeFilter' => null, 'hideCountsOnMobile' => false])

<div class="inline-flex bg-gray-200 rounded-lg p-1 sm:p-2 gap-0 sm:gap-1 md:gap-2 relative overflow-x-auto"
    style="width: fit-content; min-width: 0;">
    <!-- Animated background slider -->
    <div id="filter-slider"
        class="absolute bg-white shadow-sm rounded-md transition-all duration-300 ease-in-out pointer-events-none"
        style="height: calc(100% - 8px); top: 4px;"></div>

    @foreach ($filters as $key => $filter)
        @if (!$loop->first)
            <div class="w-px bg-gray-300 my-1 sm:my-2 md:my-3 relative z-10"></div>
        @endif
        <button onclick="{{ $filter['onclick'] ?? '' }}" id="filter-{{ $key }}"
            data-filter-key="{{ $key }}"
            class="px-3 sm:px-5 md:px-6 py-1.5 md:py-2 rounded-md text-xs sm:text-sm md:text-base font-medium transition-colors duration-200 filter-btn relative z-10 whitespace-nowrap text-gray-700">
            {{ $filter['label'] }}
            @if (isset($filter['count']))
                <span
                    class="ml-1 sm:ml-2 md:ml-3 text-gray-500 {{ $hideCountsOnMobile ? 'hidden lg:inline' : '' }}">{{ $filter['count'] }}</span>
            @endif
        </button>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('filter-slider');
        const buttons = document.querySelectorAll('.filter-btn');

        function updateSlider(button) {
            const rect = button.getBoundingClientRect();
            const container = button.parentElement.getBoundingClientRect();
            slider.style.width = rect.width + 'px';
            slider.style.left = (rect.left - container.left) + 'px';
        }

        // Initialize slider position
        const activeButton = document.querySelector(
            '.filter-btn[data-filter-key="{{ $activeFilter ?? array_key_first($filters) }}"]');
        if (activeButton) {
            updateSlider(activeButton);
        }

        // Update on window resize
        window.addEventListener('resize', () => {
            const currentActive = document.querySelector('.filter-btn.active') || activeButton;
            if (currentActive) updateSlider(currentActive);
        });
    });
</script>
