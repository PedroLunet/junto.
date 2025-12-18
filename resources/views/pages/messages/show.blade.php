@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container mx-auto px-4 py-8 h-[calc(100vh-64px)]">
    <div class="bg-white rounded-lg shadow overflow-hidden h-full flex">
        <!-- Sidebar -->
        <div class="hidden md:flex md:w-1/3 border-r border-gray-200 flex-col h-full">
            <x-messages.sidebar :friends="$friends" :activeFriendId="$friend->id" />
        </div>

        <!-- Chat Area -->
        <div class="w-full md:w-2/3 flex flex-col h-full">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <div class="flex items-center">
                    <a href="{{ route('messages.index') }}" class="mr-4 md:hidden text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <a href="{{ route('profile.show', $friend->username) }}" class="flex items-center hover:bg-gray-100 p-2 rounded-lg transition duration-150">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}">
                        <div class="ml-3">
                            <h2 class="text-lg font-semibold text-gray-800">{{ $friend->name }}</h2>
                            <p class="text-xs text-gray-500">@ {{ $friend->username }}</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                @foreach($messages as $message)
                    <div class="flex {{ $message->senderid === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] rounded-lg px-4 py-2 {{ $message->senderid === auth()->id() ? 'bg-purple-600 text-white' : 'bg-white text-gray-800 border border-gray-200' }}">
                            <p class="text-sm">{{ $message->content }}</p>
                            <p class="text-xs mt-1 {{ $message->senderid === auth()->id() ? 'text-purple-200' : 'text-gray-400' }}">
                                {{ $message->sentat->format('H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-gray-200 bg-white">
                <form id="message-form" class="flex gap-2">
                    @csrf
                    <input type="text" id="message-input" name="content" 
                        class="flex-1 rounded-full border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 px-4 py-2"
                        placeholder="Type a message..." autocomplete="off">
                    <button type="submit" class="bg-purple-600 text-white rounded-full p-3 hover:bg-purple-700 transition duration-150 flex items-center justify-center w-12 h-12">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const friendId = {{ $friend->id }};
    const currentUserId = {{ auth()->id() }};
    let lastMessageCount = {{ count($messages) }};

    // scroll to bottom on load
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

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
            if (data.status !== 'success') {
                console.error('Message send failed');
                // mudar depois
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);

        });
    });

    function appendMessage(message, isMine) {
        const div = document.createElement('div');
        div.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = `max-w-[70%] rounded-lg px-4 py-2 ${isMine ? 'bg-purple-600 text-white' : 'bg-white text-gray-800 border border-gray-200'}`;
        
        const text = document.createElement('p');
        text.className = 'text-sm';
        text.textContent = message.content;
        
        const time = document.createElement('p');
        time.className = `text-xs mt-1 ${isMine ? 'text-purple-200' : 'text-gray-400'}`;
        
        const date = new Date(message.sentat);
        time.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });

        bubble.appendChild(text);
        bubble.appendChild(time);
        div.appendChild(bubble);
        messagesContainer.appendChild(div);
    }

    // polling for new messages
    setInterval(() => {
        fetch(`{{ route('messages.fetch', $friend->id) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > lastMessageCount) {
                
                const newMessages = data.messages.slice(lastMessageCount);
                newMessages.forEach(msg => {
                    appendMessage(msg, msg.senderid === currentUserId);
                });
                
                if (newMessages.length > 0) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    lastMessageCount = data.messages.length;
                }
            }
        })
        .catch(error => console.error('Error polling messages:', error));
    }, 3000);
</script>
@endsection
