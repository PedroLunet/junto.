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
                <div class="mb-6 flex justify-between items-center">
                    <div></div>
                    <button id="snoozeBtn" onclick="toggleSnoozeUI()" 
                        class="inline-flex items-center text-sm text-white font-semibold px-4 py-2 rounded-lg bg-[#820263] hover:bg-[#600149] transition-colors">
                        <i class="fas fa-moon mr-2"></i>
                        <span id="snoozeText">Snooze Alerts</span>
                    </button>
                </div>
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

    <div id="snoozeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8 max-w-sm w-full mx-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Snooze Notifications</h2>
            <p class="text-gray-600 mb-6">Choose how long to disable notification alerts:</p>
            
            <div class="space-y-3 mb-6">
                <button onclick="snoozeNotifications(15)" class="w-full px-4 py-2 text-left rounded-lg border-2 border-gray-300 hover:border-[#820263] hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900">15 minutes</span>
                </button>
                <button onclick="snoozeNotifications(30)" class="w-full px-4 py-2 text-left rounded-lg border-2 border-gray-300 hover:border-[#820263] hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900">30 minutes</span>
                </button>
                <button onclick="snoozeNotifications(60)" class="w-full px-4 py-2 text-left rounded-lg border-2 border-gray-300 hover:border-[#820263] hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900">1 hour</span>
                </button>
                <button onclick="snoozeNotifications(240)" class="w-full px-4 py-2 text-left rounded-lg border-2 border-gray-300 hover:border-[#820263] hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900">4 hours</span>
                </button>
                <button onclick="snoozeNotifications(1440)" class="w-full px-4 py-2 text-left rounded-lg border-2 border-gray-300 hover:border-[#820263] hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900">1 day</span>
                </button>
            </div>

            <div class="flex gap-3">
                <button onclick="closeSnoozeModal()" class="flex-1 px-4 py-2 rounded-lg border-2 border-gray-300 text-gray-900 font-semibold hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        function checkSnoozeStatus() {
            fetch('/notifications/snooze/status', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const btn = document.getElementById('snoozeBtn');
                const text = document.getElementById('snoozeText');
                if (data.snoozed) {
                    btn.classList.add('bg-green-600', 'hover:bg-green-700');
                    btn.classList.remove('bg-[#820263]', 'hover:bg-[#600149]');
                    text.textContent = 'Alerts Snoozed';
                } else {
                    btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    btn.classList.add('bg-[#820263]', 'hover:bg-[#600149]');
                    text.textContent = 'Snooze Alerts';
                }
            });
        }

        function toggleSnoozeUI() {
            fetch('/notifications/snooze/status', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.snoozed) {
                    clearSnooze();
                } else {
                    showSnoozeModal();
                }
            });
        }

        function showSnoozeModal() {
            document.getElementById('snoozeModal').classList.remove('hidden');
        }

        function closeSnoozeModal() {
            document.getElementById('snoozeModal').classList.add('hidden');
        }

        function snoozeNotifications(minutes) {
            fetch('/notifications/snooze', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ duration: minutes })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeSnoozeModal();
                    updateUnreadCount();
                    checkSnoozeStatus();
                    showSnoozeConfirmation(minutes);
                }
            });
        }

        function clearSnooze() {
            fetch('/notifications/snooze/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateUnreadCount();
                    checkSnoozeStatus();
                    showClearConfirmation();
                }
            });
        }

        function showSnoozeConfirmation(minutes) {
            let timeText = minutes + ' minutes';
            if (minutes === 60) timeText = '1 hour';
            else if (minutes === 240) timeText = '4 hours';
            else if (minutes === 1440) timeText = '1 day';

            const confirmation = document.createElement('div');
            confirmation.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
            confirmation.textContent = 'Alerts snoozed for ' + timeText;
            document.body.appendChild(confirmation);

            setTimeout(() => confirmation.remove(), 3000);
        }

        function showClearConfirmation() {
            const confirmation = document.createElement('div');
            confirmation.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg';
            confirmation.textContent = 'Snooze disabled - alerts resumed';
            document.body.appendChild(confirmation);

            setTimeout(() => confirmation.remove(), 3000);
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

        document.addEventListener('DOMContentLoaded', function() {
            checkSnoozeStatus();
        });
    </script>
@endsection

