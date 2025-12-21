<div class="flex flex-col h-full">
    <!-- Fixed tabs header -->
    <div class="shrink-0 flex gap-3 mb-4 px-10">
        @foreach ($tabs as $key => $tab)
            <button id="{{ $key }}-tab"
                class="flex-1 pb-3 px-4 text-lg font-medium transition-colors text-center {{ $loop->first ? 'text-[#820273] border-b-4 border-[#820273]' : 'border-b-4 border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $tab['title'] }}
            </button>
        @endforeach
    </div>

    <div class="flex-1 overflow-y-auto">
        @foreach ($tabs as $key => $tab)
            <div id="{{ $key }}-content" class="tab-content px-10 {{ $loop->first ? '' : 'hidden' }}">
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
                    'flex-1 pb-3 px-4 text-lg font-medium transition-colors text-center border-b-4 border-transparent text-gray-500 hover:text-gray-700';
                document.getElementById('{{ $otherKey }}-content').classList.add('hidden');
            @endforeach

            document.getElementById('{{ $key }}-tab').className =
                'flex-1 pb-3 px-4 text-lg font-medium transition-colors text-center text-[#820273] border-b-4 border-[#820273]';
            document.getElementById('{{ $key }}-content').classList.remove('hidden');
        });
    @endforeach
</script>
