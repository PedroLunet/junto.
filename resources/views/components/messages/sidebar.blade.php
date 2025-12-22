@props(['activeChats', 'otherFriends', 'activeFriendId' => null])

<div class="w-full bg-white border-r border-gray-200 flex flex-col h-full">
    <div class="p-4 border-b border-gray-200">
        <div class="relative">
            <input type="text" id="chat-search" placeholder="Search chats..." 
                   class="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:border-[#624452] focus:ring-1 focus:ring-[#624452]">
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
                               class="chat-link block hover:bg-gray-50 transition duration-150 ease-in-out {{ $activeFriendId == $friend->id ? 'bg-purple-50 border-l-4 border-[#624452]' : '' }}"
                               data-friend-id="{{ $friend->id }}">
                                <div class="flex items-center px-4 py-4 sm:px-6">
                                    <div class="min-w-0 flex-1 flex items-center">
                                        <div class="flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}" onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
                                        </div>
                                        <div class="min-w-0 flex-1 px-4">
                                            <p class="text-sm font-medium {{ $activeFriendId == $friend->id ? 'text-[#624452]' : 'text-gray-900' }} truncate friend-name">{{ $friend->name }}</p>
                                            <p class="text-xs text-gray-500 truncate friend-last-message">
                                                @if($friend->last_message_sender_id === auth()->id())
                                                    @if($friend->last_message_is_read)
                                                        <i class="fa-solid fa-check-double text-blue-400 text-[10px] mr-1"></i>
                                                    @else
                                                        <i class="fa-solid fa-check-double text-gray-300 text-[10px] mr-1"></i>
                                                    @endif
                                                @endif
                                                {{ $friend->last_message }}
                                            </p>
                                            <p class="hidden friend-username">{{ $friend->username }}</p>
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
                           class="chat-link block hover:bg-gray-50 transition duration-150 ease-in-out"
                           data-friend-id="{{ $friend->id }}">
                            <div class="flex items-center px-4 py-4 sm:px-6">
                                <div class="min-w-0 flex-1 flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}" onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
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
    // search logic
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

    // ajax navigation logic
    document.addEventListener('DOMContentLoaded', function() {
        const chatLinks = document.querySelectorAll('.chat-link');
        const chatAreaContainer = document.getElementById('chat-area-container');
        const sidebarContainer = document.getElementById('sidebar-container');

        chatLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                const friendId = this.dataset.friendId;

                // update active state in sidebar
                document.querySelectorAll('.chat-link').forEach(l => {
                    l.classList.remove('bg-purple-50', 'border-l-4', 'border-[#624452]');
                    l.querySelector('.friend-name').classList.remove('text-[#624452]');
                    l.querySelector('.friend-name').classList.add('text-gray-900');
                });
                this.classList.add('bg-purple-50', 'border-l-4', 'border-[#624452]');
                this.querySelector('.friend-name').classList.remove('text-gray-900');
                this.querySelector('.friend-name').classList.add('text-[#624452]');

                // handle mobile view
                if (window.innerWidth < 768) {
                    if (sidebarContainer) sidebarContainer.classList.add('hidden');
                    if (chatAreaContainer) {
                        chatAreaContainer.classList.remove('hidden');
                
                        chatAreaContainer.classList.add('w-full'); 
                    }
                }

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    chatAreaContainer.innerHTML = html;
                    
                    const scripts = chatAreaContainer.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(script.innerHTML));
                        script.parentNode.replaceChild(newScript, script);
                    });

                    // update url
                    history.pushState(null, '', url);
                })
                .catch(error => console.error('Error loading chat:', error));
            });
        });
    });
</script>
