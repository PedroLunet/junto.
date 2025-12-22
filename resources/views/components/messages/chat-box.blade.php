@props(['friend', 'messages'])

<div class="flex flex-col h-full">
<!-- Header -->
<div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50 flex-shrink-0">
    <div class="flex items-center">
        <a href="{{ route('messages.index') }}" class="mr-4 md:hidden text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <a href="{{ route('profile.show', $friend->username) }}" class="flex items-center hover:bg-gray-100 p-2 rounded-lg transition duration-150">
            <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}" onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
            <div class="ml-3">
                <h2 class="text-lg font-semibold text-gray-800">{{ $friend->name }}</h2>
                <p class="text-xs text-gray-500">@ {{ $friend->username }}</p>
            </div>
        </a>
    </div>
    
    <!-- Options Menu -->
    <div class="relative">
        <button id="chat-options-btn" class="text-gray-500 hover:text-gray-700 p-2 rounded-full hover:bg-gray-200 focus:outline-none transition duration-150">
            <i class="fa-solid fa-ellipsis-vertical text-xl"></i>
        </button>
        <!-- Dropdown -->
        <div id="chat-options-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
            <button onclick="openDeleteModal()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition duration-150">
                <i class="fa-solid fa-trash mr-2"></i> Delete Chat
            </button>
        </div>
    </div>
</div>

<!-- Messages Area -->
<div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
    @php
        $lastDate = null;
    @endphp
    @foreach($messages as $message)
        @php
            $messageDate = $message->sentat->format('Y-m-d');
            $displayDate = null;
            
            if ($lastDate !== $messageDate) {
                if ($message->sentat->isToday()) {
                    $displayDate = 'Today';
                } elseif ($message->sentat->isYesterday()) {
                    $displayDate = 'Yesterday';
                } else {
                    $displayDate = $message->sentat->format('F j, Y');
                }
                $lastDate = $messageDate;
            }
        @endphp

        @if($displayDate)
            <div class="flex justify-center my-4">
                <span class="text-xs font-medium text-gray-500 bg-gray-200 px-3 py-1 rounded-full">
                    {{ $displayDate }}
                </span>
            </div>
        @endif

        <div id="message-{{ $message->id }}" class="flex {{ $message->senderid === auth()->id() ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[70%] px-4 py-2 {{ $message->senderid === auth()->id() ? 'bg-[#624452] text-white rounded-2xl rounded-br-none' : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-none' }}">
                <p class="text-sm">{{ $message->content }}</p>
                <div class="flex items-center justify-end gap-1 mt-1">
                    <p class="text-xs {{ $message->senderid === auth()->id() ? 'text-purple-200' : 'text-gray-400' }}">
                        {{ $message->sentat->format('H:i') }}
                    </p>
                    @if($message->senderid === auth()->id())
                        <span class="read-status ml-1">
                            @if($message->isread)
                                <i class="fa-solid fa-check-double text-blue-400 text-xs"></i>
                            @else
                                <i class="fa-solid fa-check-double text-gray-300 text-xs"></i>
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Input Area -->
<div class="p-4 border-t border-gray-200 bg-white flex-shrink-0">
    <form id="message-form" class="flex gap-2">
        @csrf
        <fieldset class="w-full flex gap-2">
            <legend class="sr-only">Message Form</legend>
            <label for="message-input" class="sr-only">Type a message</label>
            <input type="text" id="message-input" name="content" 
                class="flex-1 rounded-full border-gray-300 focus:border-[#624452] focus:ring focus:ring-purple-200 focus:ring-opacity-50 px-4 py-2"
                placeholder="Type a message..." autocomplete="off">
            <button type="submit" class="bg-[#624452] text-white rounded-full p-3 hover:bg-[#624452] transition duration-150 flex items-center justify-center w-12 h-12">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </fieldset>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-[60] flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
    <div class="relative p-5 border w-96 shadow-2xl rounded-xl bg-white transform transition-all scale-100">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">Delete Chat</h3>
            <div class="mt-2 px-2 py-2">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete this chat? This action cannot be undone.
                </p>
            </div>
            <div class="mt-5 flex flex-col gap-3">
                <form action="{{ route('messages.destroy', $friend->id) }}" method="POST" class="w-full">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" class="w-full">
                        Delete
                    </x-ui.button>
                </form>
                <x-ui.button onclick="closeDeleteModal()" variant="secondary" class="w-full">
                    Cancel
                </x-ui.button>
            </div>
        </div>
    </div>
</div>

<script>
    // clean up previous intervals if any
    if (window.chatPollingInterval) {
        clearInterval(window.chatPollingInterval);
    }

    (function() {
        const messagesContainer = document.getElementById('messages-container');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const friendId = {{ $friend->id }};
        const currentUserId = {{ auth()->id() }};
        let lastMessageCount = {{ count($messages) }};
        let lastMessageDate = "{{ $messages->last() ? $messages->last()->sentat->format('Y-m-d') : '' }}";

        // scroll to bottom on load
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const content = messageInput.value.trim();
                if (!content) return;

                const tempMessage = {
                    content: content,
                    sentat: new Date().toISOString(),
                    senderid: currentUserId
                };
                
                appendMessage(tempMessage, true);
                messageInput.value = ''; // clear input immediately
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                lastMessageCount++; 

                fetch(`{{ route('messages.store', $friend->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content: content })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // update the temp message with real ID
                        const lastMessage = messagesContainer.lastElementChild;
                        if (lastMessage && !lastMessage.id) {
                            lastMessage.id = `message-${data.message.id}`;
                        }
                    } else {
                        console.error('Message send failed');
                    }
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                });
            });
        }

        function appendMessage(message, isMine) {
            const messageDate = new Date(message.sentat);
            const dateString = messageDate.getFullYear() + '-' + String(messageDate.getMonth() + 1).padStart(2, '0') + '-' + String(messageDate.getDate()).padStart(2, '0');

            if (lastMessageDate !== dateString) {
                const separatorDiv = document.createElement('div');
                separatorDiv.className = 'flex justify-center my-4';
                
                const span = document.createElement('span');
                span.className = 'text-xs font-medium text-gray-500 bg-gray-200 px-3 py-1 rounded-full';
                
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                
                const todayString = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                const yesterdayString = yesterday.getFullYear() + '-' + String(yesterday.getMonth() + 1).padStart(2, '0') + '-' + String(yesterday.getDate()).padStart(2, '0');

                if (dateString === todayString) {
                    span.textContent = 'Today';
                } else if (dateString === yesterdayString) {
                    span.textContent = 'Yesterday';
                } else {
                    span.textContent = messageDate.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
                }

                separatorDiv.appendChild(span);
                messagesContainer.appendChild(separatorDiv);
                
                lastMessageDate = dateString;
            }

            const div = document.createElement('div');
            div.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;
            if (message.id) div.id = `message-${message.id}`;
            
            const bubble = document.createElement('div');
            bubble.className = `max-w-[70%] px-4 py-2 ${isMine ? 'bg-[#624452] text-white rounded-2xl rounded-br-none' : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-none'}`;
            
            const text = document.createElement('p');
            text.className = 'text-sm';
            text.textContent = message.content;
            
            const metaDiv = document.createElement('div');
            metaDiv.className = 'flex items-center justify-end gap-1 mt-1';

            const time = document.createElement('p');
            time.className = `text-xs ${isMine ? 'text-purple-200' : 'text-gray-400'}`;
            
            const date = new Date(message.sentat);
            time.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });

            metaDiv.appendChild(time);

            if (isMine) {
                const statusSpan = document.createElement('span');
                statusSpan.className = 'read-status ml-1';
                const icon = document.createElement('i');
                icon.className = `fa-solid fa-check-double text-xs ${message.isread ? 'text-blue-400' : 'text-gray-300'}`;
                statusSpan.appendChild(icon);
                metaDiv.appendChild(statusSpan);
            }

            bubble.appendChild(text);
            bubble.appendChild(metaDiv);
            div.appendChild(bubble);
            messagesContainer.appendChild(div);
        }

        // polling for new messages
        window.chatPollingInterval = setInterval(() => {
            fetch(`{{ route('messages.fetch', $friend->id) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages) {
                    // update read status for existing messages
                    data.messages.forEach(msg => {
                        if (msg.senderid === currentUserId) {
                            const msgElement = document.getElementById(`message-${msg.id}`);
                            if (msgElement) {
                                const statusIcon = msgElement.querySelector('.read-status i');
                                if (statusIcon) {
                                    if (msg.isread) {
                                        statusIcon.classList.remove('text-gray-300');
                                        statusIcon.classList.add('text-blue-400');
                                    } else {
                                        statusIcon.classList.remove('text-blue-400');
                                        statusIcon.classList.add('text-gray-300');
                                    }
                                }
                            }
                        }
                    });

                    if (data.messages.length > lastMessageCount) {
                        const newMessages = data.messages.slice(lastMessageCount);
                        newMessages.forEach(msg => {
                            appendMessage(msg, msg.senderid === currentUserId);
                        });
                        
                        if (newMessages.length > 0) {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            lastMessageCount = data.messages.length;
                        }
                    }
                }
            })
            .catch(error => console.error('Error polling messages:', error));
        }, 3000);

        const optionsBtn = document.getElementById('chat-options-btn');
        const optionsMenu = document.getElementById('chat-options-menu');
        const deleteModal = document.getElementById('delete-modal');

        if (optionsBtn && optionsMenu) {
            optionsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                optionsMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!optionsMenu.contains(e.target) && !optionsBtn.contains(e.target)) {
                    optionsMenu.classList.add('hidden');
                }
            });
        }

        window.openDeleteModal = function() {
            if (deleteModal) {
                deleteModal.classList.remove('hidden');
                if (optionsMenu) optionsMenu.classList.add('hidden');
            }
        }

        window.closeDeleteModal = function() {
            if (deleteModal) {
                deleteModal.classList.add('hidden');
            }
        }
 
        if (deleteModal) {
            deleteModal.addEventListener('click', (e) => {
                if (e.target === deleteModal) {
                    closeDeleteModal();
                }
            });
        }
    })();
</script>
</div>