@props(['friends', 'activeFriendId' => null])

<div class="w-full bg-white border-r border-gray-200 flex flex-col h-full">
    <div class="p-4 border-b border-gray-200">
        <div class="relative">
            <input type="text" id="chat-search" placeholder="Search chats..." 
                   class="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:border-purple-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            </div>
        </div>
    </div>
    
    <div class="overflow-y-auto flex-1">
        @if($friends->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>No friends yet.</p>
                <a href="{{ route('search.users') }}" class="text-purple-600 hover:underline mt-2 inline-block">Find friends</a>
            </div>
        @else
            <ul id="chat-list" class="divide-y divide-gray-200">
                @foreach($friends as $friend)
                    <li class="chat-item">
                        <a href="{{ route('messages.show', $friend->id) }}" 
                           class="block hover:bg-gray-50 transition duration-150 ease-in-out {{ $activeFriendId == $friend->id ? 'bg-purple-50 border-l-4 border-purple-600' : '' }}">
                            <div class="flex items-center px-4 py-4 sm:px-6">
                                <div class="min-w-0 flex-1 flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}">
                                    </div>
                                    <div class="min-w-0 flex-1 px-4">
                                        <p class="text-sm font-medium {{ $activeFriendId == $friend->id ? 'text-purple-700' : 'text-gray-900' }} truncate friend-name">{{ $friend->name }}</p>
                                        <p class="text-xs text-gray-500 truncate friend-username">@ {{ $friend->username }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<script>
    document.getElementById('chat-search').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const chatItems = document.querySelectorAll('.chat-item');
        
        chatItems.forEach(item => {
            const name = item.querySelector('.friend-name').textContent.toLowerCase();
            const username = item.querySelector('.friend-username').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || username.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
