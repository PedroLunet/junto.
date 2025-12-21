<div id="notificationAlertContainer" class="fixed top-20 right-6 z-50 max-w-sm pointer-events-none"></div>

<script>
    let lastNotificationId = null;
    let isInitialized = false;

    async function fetchLatestNotification() {
        if (!window.isAuthenticated) return;

        try {
            const response = await fetch('/notifications/latest-unread', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            
            if (!isInitialized) {
                lastNotificationId = data.notification?.id || null;
                isInitialized = true;
                return;
            }
            
            if (data.notification && data.notification.id !== lastNotificationId) {
                lastNotificationId = data.notification.id;
                showNotificationAlert(data.notification);
            }
        } catch (error) {
            console.error('Error fetching notification:', error);
        }
    }

    function showNotificationAlert(notification) {
        const container = document.getElementById('notificationAlertContainer');
        const alertId = 'notification-alert-' + notification.id;
        
        let messageText = notification.message || 'New notification';

        const alertHTML = `
            <div id="${alertId}" class="mb-3 bg-white rounded-lg shadow-lg border-l-4 border-purple-600 p-4 pointer-events-auto animate-slide-in">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 text-purple-600">
                        <i class="fas fa-bell text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">Notification</p>
                        <p class="text-sm text-gray-600 mt-1 break-words">${escapeHtml(messageText)}</p>
                    </div>
                    <button onclick="document.getElementById('${alertId}').remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', alertHTML);

        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchLatestNotification();
        setInterval(fetchLatestNotification, 15000);
    });
</script>

<style>
    @keyframes slide-in {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
</style>
