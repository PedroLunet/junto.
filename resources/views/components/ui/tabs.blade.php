<div class="flex flex-col h-full">
    <div class="shrink-0 flex gap-3 mb-4 px-4 sm:px-8 md:px-10 overflow-x-auto">
        @foreach ($tabs as $key => $tab)
            <button id="{{ $key }}-tab"
                class="flex-1 pb-3 px-2 sm:px-4 text-sm sm:text-lg font-medium transition-colors text-center whitespace-nowrap {{ $loop->first ? 'text-[#820273] border-b-4 border-[#820273]' : 'border-b-4 border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $tab['title'] }}
            </button>
        @endforeach
    </div>

    <div class="flex-1 overflow-y-auto">
        @foreach ($tabs as $key => $tab)
            <div id="{{ $key }}-content" class="tab-content px-4 sm:px-8 md:px-10 {{ $loop->first ? '' : 'hidden' }}">
                {!! $tab['content'] !!}
            </div>
        @endforeach
    </div>
</div>

<script>
    @foreach ($tabs as $key => $tab)
        document.getElementById('{{ $key }}-tab').addEventListener('click', function() {
            @foreach ($tabs as $otherKey => $otherTab)
                document.getElementById('{{ $otherKey }}-tab').className =
                    'flex-1 pb-3 px-2 sm:px-4 text-sm sm:text-lg font-medium transition-colors text-center whitespace-nowrap border-b-4 border-transparent text-gray-500 hover:text-gray-700';
                document.getElementById('{{ $otherKey }}-content').classList.add('hidden');
            @endforeach

            document.getElementById('{{ $key }}-tab').className =
                'flex-1 pb-3 px-2 sm:px-4 text-sm sm:text-lg font-medium transition-colors text-center whitespace-nowrap text-[#820273] border-b-4 border-[#820273]';
            document.getElementById('{{ $key }}-content').classList.remove('hidden');
        });
    @endforeach
</script>
