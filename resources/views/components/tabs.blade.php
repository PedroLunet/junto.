<div class="flex flex-col">
    <!-- tabs -->
    <div class="flex gap-4 mb-6">
        @foreach($tabs as $key => $tab)
            <button 
                id="{{ $key }}-tab"
                class="flex-1 {{ $loop->first ? 'bg-gray-400 text-white hover:bg-gray-500' : 'bg-gray-300 hover:bg-gray-400 text-gray-700' }} py-3 px-6 rounded-lg text-xl font-semibold transition-colors">
                {{ $tab['title'] }}
            </button>
        @endforeach
    </div>

    <!-- tab Contents -->
    @foreach($tabs as $key => $tab)
        <div id="{{ $key }}-content" class="tab-content {{ $loop->first ? '' : 'hidden' }}">
            {!! $tab['content'] !!}
        </div>
    @endforeach
</div>

<script>
    @foreach($tabs as $key => $tab)
        document.getElementById('{{ $key }}-tab').addEventListener('click', function() {
            // reset all tabs to inactive state
            @foreach($tabs as $otherKey => $otherTab)
                document.getElementById('{{ $otherKey }}-tab').className = 'flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg text-xl font-semibold transition-colors';
                document.getElementById('{{ $otherKey }}-content').classList.add('hidden');
            @endforeach
            
            // set clicked tab to active state
            document.getElementById('{{ $key }}-tab').className = 'flex-1 bg-gray-400 text-white py-3 px-6 rounded-lg text-xl font-semibold transition-colors hover:bg-gray-500';
            document.getElementById('{{ $key }}-content').classList.remove('hidden');
        });
    @endforeach
</script>