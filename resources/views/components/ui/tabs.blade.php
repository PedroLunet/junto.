<div class="flex flex-col h-full">
    <!-- fixed tabs header -->
    <div class="shrink-0 flex gap-3 sm:gap-4 mb-4 sm:mb-6 px-2 sm:px-8 lg:px-10">
        @foreach ($tabs as $key => $tab)
            <button id="{{ $key }}-tab"
                class="flex-1 pb-3 sm:pb-4 px-2 sm:px-3 lg:px-4 text-base sm:text-base lg:text-lg font-medium transition-colors text-center {{ $loop->first ? 'text-[#820273] border-b-4 border-[#820273]' : 'border-b-4 border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $tab['title'] }}
            </button>
        @endforeach
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
    @foreach ($tabs as $key => $tab)
        document.getElementById('{{ $key }}-tab').addEventListener('click', function() {
            // reset all tabs to inactive state
            @foreach ($tabs as $otherKey => $otherTab)
                document.getElementById('{{ $otherKey }}-tab').className =
                    'flex-1 pb-3 sm:pb-4 px-2 sm:px-4 lg:px-6 text-base sm:text-base lg:text-lg font-medium transition-colors text-center border-b-4 border-transparent text-gray-500 hover:text-gray-700';
                document.getElementById('{{ $otherKey }}-content').classList.add('hidden');
            @endforeach

            // set clicked tab to active state
            document.getElementById('{{ $key }}-tab').className =
                'flex-1 pb-2 sm:pb-4 px-2 sm:px-3 lg:px-4 text-base sm:text-base lg:text-lg font-medium transition-colors text-center text-[#820273] border-b-4 border-[#820273]';
            document.getElementById('{{ $key }}-content').classList.remove('hidden');
        });
    @endforeach
</script>
