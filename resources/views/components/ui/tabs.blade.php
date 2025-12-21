<div class="flex flex-col h-full">

    <div class="relative shrink-0 flex gap-0 border-b border-gray-200 mb-4 sm:mb-6 px-2 sm:px-8 lg:px-10" id="tabs-bar">
        @foreach ($tabs as $key => $tab)
            <button id="{{ $key }}-tab"
                class="tab-btn flex-1 pb-3 sm:pb-4 px-2 sm:px-3 lg:px-4 text-base sm:text-base lg:text-lg font-medium transition-colors text-center border-b-4 {{ $loop->first ? 'text-[#820273] border-transparent z-10' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                style="min-width:0; position:relative;">
                {{ $tab['title'] }}
            </button>
        @endforeach
        <span id="tab-underline" class="absolute bottom-0 h-1 bg-[#820273] rounded transition-all duration-300"
            style="left:0;width:0;"></span>
    </div>

    <!-- scrollable tab contents -->
    <div class="flex-1 overflow-y-auto">
        @foreach ($tabs as $key => $tab)
            <div id="{{ $key }}-content"
                class="tab-content px-2 sm:px-8 lg:px-10 {{ $loop->first ? '' : 'hidden' }}">
                {!! $tab['content'] !!}
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabKeys = [
            @foreach ($tabs as $key => $tab)
                '{{ $key }}'
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        ];
        const underline = document.getElementById('tab-underline');
        const tabBar = document.getElementById('tabs-bar');

        function moveUnderlineToTab(tabEl) {
            const barRect = tabBar.getBoundingClientRect();
            const tabRect = tabEl.getBoundingClientRect();
            underline.style.left = (tabRect.left - barRect.left) + 'px';
            underline.style.width = tabRect.width + 'px';
        }
        // Initial position
        const firstTab = document.getElementById(tabKeys[0] + '-tab');
        moveUnderlineToTab(firstTab);
        @foreach ($tabs as $key => $tab)
            document.getElementById('{{ $key }}-tab').addEventListener('click', function() {
                // reset all tabs to inactive state
                @foreach ($tabs as $otherKey => $otherTab)
                    document.getElementById('{{ $otherKey }}-tab').className =
                        'tab-btn flex-1 pb-3 sm:pb-4 px-2 sm:px-3 lg:px-4 text-base sm:text-base lg:text-lg font-medium transition-colors text-center border-b-4 border-transparent text-gray-500 hover:text-gray-700';
                    document.getElementById('{{ $otherKey }}-content').classList.add('hidden');
                @endforeach

                // set clicked tab to active state
                document.getElementById('{{ $key }}-tab').className =
                    'tab-btn flex-1 pb-3 sm:pb-4 px-2 sm:px-3 lg:px-4 text-base sm:text-base lg:text-lg font-medium transition-colors text-center text-[#820273] border-b-4 border-transparent z-10';
                document.getElementById('{{ $key }}-content').classList.remove('hidden');
                moveUnderlineToTab(this);
            });
        @endforeach
        // Responsive: move underline on window resize
        window.addEventListener('resize', function() {
            const activeTab = tabKeys.map(k => document.getElementById(k + '-tab')).find(tab => tab
                .classList.contains('text-[#820273]'));
            if (activeTab) moveUnderlineToTab(activeTab);
        });
    });
</script>
