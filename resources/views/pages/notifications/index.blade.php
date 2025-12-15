@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="flex flex-col w-full">
        <!-- Header Section -->
        <div class="w-full bg-gray-50 py-6 md:py-8 border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Notifications</h1>
                    @if($notifications->count() > 0)
                        <button onclick="markAllAsRead()" class="text-sm md:text-base text-blue-600 hover:text-blue-700 font-medium">
                            Mark all as read
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="w-full flex-1">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8 py-6 md:py-8">
                @if($notifications->count() > 0)
                    <div class="space-y-2">
                        @foreach($notifications as $notification)
                            @php
                                $isSnoozed = $notification->snoozed_until && $notification->snoozed_until > now();
                            @endphp
                            <div class="notification-item bg-white border-l-4 {{ $notification->isread ? 'border-gray-300' : 'border-blue-500' }} p-4 rounded hover:bg-gray-50 transition"
                                id="notification-{{ $notification->id }}">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-gray-900 {{ !$notification->isread ? 'font-semibold' : 'font-normal' }}">
                                            {{ $notification->message }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ $notification->createdat->diffForHumans() }}
                                        </p>
                                        @if($isSnoozed)
                                            <p class="text-xs text-orange-600 mt-1">
                                                Snoozed until {{ $notification->snoozed_until->format('M d, H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex gap-2 flex-shrink-0">
                                        @if(!$notification->isread)
                                            <button onclick="markAsRead({{ $notification->id }})" 
                                                class="text-xs md:text-sm text-blue-600 hover:text-blue-700 font-medium px-2 py-1">
                                                Mark read
                                            </button>
                                        @endif
                                        @if(!$isSnoozed)
                                            <div class="relative">
                                                <button onclick="toggleSnoozeMenu(event, {{ $notification->id }})" class="text-xs md:text-sm text-gray-600 hover:text-gray-700 font-medium px-2 py-1">
                                                    Snooze
                                                </button>
                                                <div id="snooze-menu-{{ $notification->id }}" class="hidden absolute left-1/2 transform -translate-x-1/2 mt-2 bg-white border border-gray-200 rounded shadow-lg z-20 min-w-max">
                                                    <button onclick="snoozeNotification({{ $notification->id }}, 30)" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        30 minutes
                                                    </button>
                                                    <button onclick="snoozeNotification({{ $notification->id }}, 60)" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        1 hour
                                                    </button>
                                                    <button onclick="snoozeNotification({{ $notification->id }}, 480)" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        8 hours
                                                    </button>
                                                    <button onclick="snoozeNotification({{ $notification->id }}, 1440)" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        1 day
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <button onclick="unsnoozeNotification({{ $notification->id }})" 
                                                class="text-xs md:text-sm text-gray-600 hover:text-gray-700 font-medium px-2 py-1">
                                                Unsnooze
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($notifications->hasPages())
                        <div class="mt-8 flex justify-center gap-2">
                            @if ($notifications->onFirstPage())
                                <span class="px-3 py-2 text-gray-400">← Previous</span>
                            @else
                                <a href="{{ $notifications->previousPageUrl() }}" class="px-3 py-2 text-blue-600 hover:text-blue-700">← Previous</a>
                            @endif

                            @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                                @if ($page == $notifications->currentPage())
                                    <span class="px-3 py-2 bg-blue-600 text-white rounded">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3 py-2 text-blue-600 hover:text-blue-700">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($notifications->hasMorePages())
                                <a href="{{ $notifications->nextPageUrl() }}" class="px-3 py-2 text-blue-600 hover:text-blue-700">Next →</a>
                            @else
                                <span class="px-3 py-2 text-gray-400">Next →</span>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-center py-16">
                        <div class="text-6xl mb-4">🔔</div>
                        <p class="text-xl text-gray-600">No notifications yet</p>
                        <p class="text-gray-500 mt-2">Check back later for updates on your activity</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.getElementById(`notification-${notificationId}`);
                    if (item) {
                        item.classList.remove('border-blue-500');
                        item.classList.add('border-gray-300');
                        item.querySelector('p').classList.remove('font-semibold');
                        item.querySelector('p').classList.add('font-normal');
                        const button = item.querySelector('button[onclick*="markAsRead"]');
                        if (button) button.remove();
                        updateUnreadCount();
                    }
                }
            });
        }

        function markAllAsRead() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function snoozeNotification(notificationId, duration) {
            fetch(`/notifications/${notificationId}/snooze`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ duration: duration })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function unsnoozeNotification(notificationId) {
            snoozeNotification(notificationId, 0);
        }

        function toggleSnoozeMenu(event, notificationId) {
            event.preventDefault();
            const menu = document.getElementById(`snooze-menu-${notificationId}`);
            menu.classList.toggle('hidden');
            
            event.stopPropagation();
            document.addEventListener('click', function closeMenu() {
                menu.classList.add('hidden');
                document.removeEventListener('click', closeMenu);
            });
        }

        function updateUnreadCount() {
            fetch('/notifications/unread-count', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
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
            });
        }
    </script>
@endsection
