<div class="flex flex-col h-full">
    <div class="shrink-0 flex gap-0 mb-0 border-b border-gray-200">
        @foreach ($tabs as $key => $tab)
            <button id="{{ $key }}-tab"
                class="pb-4 px-8 text-lg font-semibold transition-all {{ $loop->first ? 'text-[#820263] border-b-4 border-[#820263]' : 'border-b-4 border-transparent text-gray-600 hover:text-gray-900' }}">
                {{ $tab['title'] }}
            </button>
        @endforeach
    </div>

    <div class="flex-1 overflow-y-auto">
        @foreach ($tabs as $key => $tab)
            <div id="{{ $key }}-content" class="tab-content {{ $loop->first ? '' : 'hidden' }}">
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
                    'pb-4 px-8 text-lg font-semibold transition-all border-b-4 border-transparent text-gray-600 hover:text-gray-900';
                document.getElementById('{{ $otherKey }}-content').classList.add('hidden');
            @endforeach

            document.getElementById('{{ $key }}-tab').className =
                'pb-4 px-8 text-lg font-semibold transition-all text-[#820263] border-b-4 border-[#820263]';
            document.getElementById('{{ $key }}-content').classList.remove('hidden');
        });
    @endforeach
</script>
