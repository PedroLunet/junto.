@extends('layouts.app')

@section('title', 'Inbox')

@section('content')
    <div class="flex flex-col w-full">
        <div class="w-full bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 py-8 md:py-12">
                <div class="flex justify-between items-end gap-6">
                    <div>
                        <h1 class="text-5xl md:text-6xl font-black text-gray-900 tracking-tight">Inbox</h1>
                        <p class="text-gray-500 mt-3 text-lg">Stay updated with notifications and requests</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full flex-1 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 py-8">
                <x-ui.tabs :tabs="[
                    'notifications' => [
                        'title' => 'Notifications',
                        'content' => view('components.notifications.list', [
                            'notifications' => $notifications,
                        ])->render(),
                    ],
                    'friend-requests' => [
                        'title' => 'Friend Requests',
                        'content' => view('components.friend-requests.inbox-tab', [
                            'friendRequests' => $friendRequests,
                            'sentRequests' => $sentRequests,
                        ])->render(),
                    ],
                ]" />
            </div>
        </div>
    </div>

    <script>
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

