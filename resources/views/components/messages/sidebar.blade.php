@props(['activeChats', 'otherFriends', 'activeFriendId' => null])

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
        <!-- Active Chats Section -->
        <div id="active-chats-section" class="pb-2">
            <div id="active-chats-header" class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden">
                Messages
            </div>
            @if($activeChats->isEmpty())
                <div id="no-chats-message" class="p-8 text-center text-gray-500">
                    <p>No chats yet.</p>
                </div>
            @else
                <ul id="active-chat-list" class="divide-y divide-gray-200">
                    @foreach($activeChats as $friend)
                        <li class="chat-item active-chat-item">
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

        <!-- Other Friends Section -->
        <div id="other-friends-section" class="hidden">
            <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Your Friends
            </div>
            <ul id="other-friends-list" class="divide-y divide-gray-200">
                @foreach($otherFriends as $friend)
                    <li class="chat-item other-friend-item">
                        <a href="{{ route('messages.show', $friend->id) }}" 
                           class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                            <div class="flex items-center px-4 py-4 sm:px-6">
                                <div class="min-w-0 flex-1 flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}">
                                    </div>
                                    <div class="min-w-0 flex-1 px-4">
                                        <p class="text-sm font-medium text-gray-900 truncate friend-name">{{ $friend->name }}</p>
                                        <p class="text-xs text-gray-500 truncate friend-username">@ {{ $friend->username }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('chat-search').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const activeChatItems = document.querySelectorAll('.active-chat-item');
        const otherFriendItems = document.querySelectorAll('.other-friend-item');
        const otherFriendsSection = document.getElementById('other-friends-section');
        const activeChatsHeader = document.getElementById('active-chats-header');
        const noChatsMessage = document.getElementById('no-chats-message');
        
        let hasActiveMatches = false;
        let hasOtherMatches = false;

        // filter active chats
        activeChatItems.forEach(item => {
            const name = item.querySelector('.friend-name').textContent.toLowerCase();
            const username = item.querySelector('.friend-username').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || username.includes(searchTerm)) {
                item.style.display = '';
                hasActiveMatches = true;
            } else {
                item.style.display = 'none';
            }
        });

        // filter other friends
        otherFriendItems.forEach(item => {
            const name = item.querySelector('.friend-name').textContent.toLowerCase();
            const username = item.querySelector('.friend-username').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || username.includes(searchTerm)) {
                item.style.display = '';
                hasOtherMatches = true;
            } else {
                item.style.display = 'none';
            }
        });

        // toggle sections visibility
        if (searchTerm.length > 0) {
            // search Mode
            otherFriendsSection.classList.remove('hidden');
            activeChatsHeader.classList.remove('hidden');
            if (noChatsMessage) noChatsMessage.classList.add('hidden');
            
            if (!hasActiveMatches) {
                 activeChatsHeader.classList.add('hidden'); //  hide header if empty
            }
        } else {
            // default Mode
            otherFriendsSection.classList.add('hidden');
            activeChatsHeader.classList.add('hidden');
            
      
            activeChatItems.forEach(item => item.style.display = '');
            if (noChatsMessage) noChatsMessage.classList.remove('hidden');
        }
    });
</script>
