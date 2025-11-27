@php
    $type = $attributes->get('type', 'info');
    $title = $attributes->get('title', 'Alert');
    $message = $attributes->get('message', '');
    $confirmText = $attributes->get('confirmText', 'OK');
    $cancelText = $attributes->get('cancelText', 'Cancel');
    $showCancel = $attributes->get('showCancel', false);
    $onConfirm = $attributes->get('onConfirm', '');
    $onCancel = $attributes->get('onCancel', '');
@endphp

<div id="alertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 id="alertTitle" class="text-4xl font-bold text-gray-900 mt-2">{{ $title }}</h2>
            <x-button id="closeAlert" variant="ghost" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </x-button>
        </div>

        <!-- body -->
        <div class="px-8 pb-8">
            <p id="alertMessage" class="text-2xl text-gray-700 mb-12">{{ $message }}</p>

            <!-- buttons -->
            <div id="alertButtons" class="flex justify-end gap-6">
                <x-button id="alertCancel" variant="secondary" class="hidden">
                    Cancel
                </x-button>
                <x-button id="alertConfirm" variant="primary">
                    OK
                </x-button>
            </div>
        </div>
    </div>
</div>

<script>
    window.showAlert = function(options = {}) {
        const modal = document.getElementById('alertModal');
        const title = document.getElementById('alertTitle');
        const message = document.getElementById('alertMessage');
        const confirmBtn = document.getElementById('alertConfirm');
        const cancelBtn = document.getElementById('alertCancel');
        const closeBtn = document.getElementById('closeAlert');

        // Set content
        title.textContent = options.title || 'Alert';
        message.textContent = options.message || '';
        confirmBtn.textContent = options.confirmText || 'OK';
        cancelBtn.textContent = options.cancelText || 'Cancel';

        // Show/hide cancel button
        if (options.showCancel) {
            cancelBtn.classList.remove('hidden');
        } else {
            cancelBtn.classList.add('hidden');
        }

        // Set button variants based on type
        if (options.type === 'danger') {
            confirmBtn.className = confirmBtn.className.replace('variant="primary"', '');
            confirmBtn.setAttribute('data-variant', 'danger');
            confirmBtn.className += ' bg-red-600 text-white hover:bg-red-700';
        } else {
            confirmBtn.className = confirmBtn.className.replace(/bg-red-\d+ text-white hover:bg-red-\d+/, '');
            confirmBtn.setAttribute('data-variant', 'primary');
        }

        // Clear previous event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        const newCancelBtn = cancelBtn.cloneNode(true);
        const newCloseBtn = closeBtn.cloneNode(true);

        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

        // Add event listeners
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (options.onCancel) options.onCancel();
        }

        newConfirmBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (options.onConfirm) options.onConfirm();
        });

        newCancelBtn.addEventListener('click', closeModal);
        newCloseBtn.addEventListener('click', closeModal);

        // Close on ESC key
        function handleEscape(e) {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', handleEscape);
            }
        }
        document.addEventListener('keydown', handleEscape);

        // Close when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Show modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        return new Promise((resolve) => {
            newConfirmBtn.addEventListener('click', () => resolve(true));
            newCancelBtn.addEventListener('click', () => resolve(false));
            newCloseBtn.addEventListener('click', () => resolve(false));
        });
    };

    // Convenience functions
    window.alertConfirm = function(message, title = 'Confirm') {
        return showAlert({
            title: title,
            message: message,
            showCancel: true,
            confirmText: 'Yes',
            cancelText: 'No',
            type: 'danger'
        });
    };

    window.alertInfo = function(message, title = 'Information') {
        return showAlert({
            title: title,
            message: message,
            showCancel: false
        });
    };
</script>
