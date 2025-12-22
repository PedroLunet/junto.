@extends('layouts.base')

@section('header-scripts')
    function updateNotificationBadge() {
    if (!window.isAuthenticated) return;

    fetch('/notifications/unread-count', {
    headers: {
    'Accept': 'application/json'
    }
    })
    .then(response => {
    if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
    })
    .then(data => {
    const badge = document.getElementById('notification-badge');
    if (badge) {
    if (data.count > 0) {
    badge.textContent = data.count;
    badge.classList.remove('hidden');
    } else {
    badge.classList.add('hidden');
    }
    }
    })
    .catch(error => console.error('Error fetching notifications:', error));
    }

    document.addEventListener('DOMContentLoaded', updateNotificationBadge);
    setInterval(updateNotificationBadge, 30000);
@endsection

@section('header-actions')
    @auth
        <x-ui.button href="{{ route('notifications.index') }}" variant="ghost" class="p-2 relative" title="Inbox">
            <i class="fa-solid fa-inbox"></i>
            <span id="notification-badge"
                class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
        </x-ui.button>
    @endauth
    @guest
        <x-ui.button href="{{ route('friend-requests.index') }}" variant="ghost" class="p-2" title="Inbox">
            <i class="fa-solid fa-inbox"></i>
        </x-ui.button>
    @endguest
    <x-ui.button href="{{ route('search.users') }}" variant="ghost" class="p-2" title="Search">
        <i class="fa-solid fa-magnifying-glass"></i>
    </x-ui.button>
@endsection

@section('navigation')
    <ul class="space-y-2">
        <li><a href="{{ route('friends-feed') }}"
                class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white"><i class="fa-solid fa-user-group fa-fw mr-2"></i>Friends Feed</a></li>
        <li><a href="{{ route('movies') }}" class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white"><i class="fa-solid fa-clapperboard fa-fw mr-2"></i>Movies</a>
        </li>
        <li><a href="{{ route('books') }}" class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white"><i class="fa-solid fa-book fa-fw mr-2"></i>Books</a>
        </li>
        <li><a href="{{ route('music') }}" class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white"><i class="fa-solid fa-music fa-fw mr-2"></i>Music</a>
        </li>
        <li><a href="{{ route('groups.index') }}"
                class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white"><i class="fa-solid fa-people-group fa-fw mr-2"></i>Groups</a></li>
        <li><a href="{{ route('messages.index') }}"
                class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white"><i class="fa-solid fa-envelope fa-fw mr-2"></i>Messages</a></li>
        
    </ul>
@endsection

@section('sidebar-actions')
    @auth
        <x-ui.button id="regular-button" variant="special"> + </x-ui.button>
    @else
        <x-ui.button href="{{ route('login') }}" variant="special"> + </x-ui.button>
    @endauth
    <div class="flex gap-2 w-full">
        @auth
            <x-ui.button id="movie-button" variant="special" class="flex-1 justify-center">
                <i class="fa-solid fa-clapperboard"></i>
            </x-ui.button>
            <x-ui.button id="book-button" variant="special" class="flex-1 justify-center">
                <i class="fa-solid fa-book"></i>
            </x-ui.button>
            <x-ui.button id="music-button" variant="special" class="flex-1 justify-center">
                <i class="fa-solid fa-music"></i>
            </x-ui.button>
        @else
            <x-ui.button href="{{ route('login') }}" variant="special" class="flex-1 justify-center">
                <i class="fa-solid fa-clapperboard"></i>
            </x-ui.button>
            <x-ui.button href="{{ route('login') }}" variant="special" class="flex-1 justify-center">
                <i class="fa-solid fa-book"></i>
            </x-ui.button>
            <x-ui.button href="{{ route('login') }}" variant="special" class="flex-1 justify-center">
                <i class="fa-solid fa-music"></i>
            </x-ui.button>
        @endauth
    </div>
@endsection

@section('modals')
    @auth
        <x-posts.create.create-regular-modal />
        <x-posts.create.create-movie-review-modal />
        <x-posts.create.create-book-review-modal />
        <x-posts.create.create-music-review-modal />
    @endauth
@endsection
