@extends('layouts.app')

@section('content')
    <!-- alert container -->
    <div id="appealAlert" class="fixed top-4 right-4 z-60 hidden" style="max-width: 400px;"></div>

    <!-- blocked modal overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 md:p-12 text-center">
                <!-- icon -->
                <div class="mb-6 sm:mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 sm:w-32 sm:h-32 bg-red-100 rounded-full">
                        <i class="fas fa-ban text-4xl sm:text-6xl text-red-600"></i>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-3xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">
                    Account Blocked
                </h1>

                <!-- Message -->
                <p class="text-lg sm:text-xl md:text-2xl text-gray-600 mb-6 sm:mb-8">
                    Your account has been blocked.
                </p>

                @if ($hasRejectedAppeal)
                    <p class="text-base sm:text-base md:text-lg text-red-600 font-medium mb-8 sm:mb-12">
                        Your appeal has been rejected. If you have further questions, please contact support.
                    </p>
                @else
                    <p class="text-base sm:text-base md:text-lg text-gray-500 mb-8 sm:mb-12">
                        If you believe this is a mistake, please appeal for unblock.
                    </p>
                @endif


                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    @unless ($hasRejectedAppeal)
                        <x-ui.button variant="secondary" onclick="appealUnblock()"
                            class="text-lg sm:text-xl md:text-xl px-8 sm:px-10 md:px-12 py-3 sm:py-4 w-full sm:w-auto">
                            <i class="fas fa-envelope mr-2 sm:mr-3"></i>
                            Appeal for Unblock
                        </x-ui.button>
                    @endunless
                    <x-ui.button variant="primary" onclick="window.location.href='{{ route('logout') }}'"
                        class="text-lg sm:text-xl md:text-xl px-8 sm:px-10 md:px-12 py-3 sm:py-4 w-full sm:w-auto">
                        <i class="fas fa-sign-out-alt mr-2 sm:mr-3"></i>
                        Logout
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <x-blocked.appeal-modal />

    <script>
        function showAppealAlert(message, type = 'success') {
            const alertContainer = document.getElementById('appealAlert');
            const title = type === 'success' ? 'Success' : 'Error';

            alertContainer.innerHTML = `
                <div class="relative flex items-start gap-4 px-6 py-10 rounded-2xl border ${type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'} shadow-lg mb-4">
                    <div class="shrink-0 flex items-center justify-center w-12 h-12 rounded-full ${type === 'success' ? 'bg-green-200' : 'bg-red-200'}">
                        ${type === 'success' 
                            ? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" class="stroke-green-400 fill-green-50"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" class="stroke-green-600"/></svg>'
                            : '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" class="stroke-red-400 fill-red-50"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-6 6m0-6l6 6" class="stroke-red-600"/></svg>'
                        }
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-2xl ${type === 'success' ? 'text-green-600' : 'text-red-600'} mb-1">${title}</div>
                        <div class="text-gray-700 text-3xl">${message}</div>
                    </div>
                    <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.closest('div').parentElement.classList.add('hidden')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            `;
            alertContainer.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertContainer.classList.add('hidden');
            }, 5000);
        }

        async function appealUnblock() {
            document.getElementById('appealModal').classList.remove('hidden');
            document.getElementById('appealModal').classList.add('flex');
        }

        function closeAppealModal() {
            document.getElementById('appealModal').classList.add('hidden');
            document.getElementById('appealModal').classList.remove('flex');
            document.getElementById('appealForm').reset();
        }

        document.getElementById('appealForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const reason = document.getElementById('appealReason').value.trim();
            const submitBtn = document.getElementById('submitAppealBtn');

            if (!reason) {
                showAppealAlert('Please provide a reason for your appeal.', 'error');
                return;
            }

            // Disable button during submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                const response = await fetch('{{ route('appeal.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        reason
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeAppealModal();
                    showAppealAlert(
                        'Your appeal has been submitted successfully. Administrators will review it shortly.',
                        'success');
                } else {
                    closeAppealModal();
                    showAppealAlert(data.message || 'Failed to submit appeal. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                closeAppealModal();
                showAppealAlert('An error occurred while submitting your appeal. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Appeal';
            }
        });

        document.getElementById('appealModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAppealModal();
            }
        });
    </script>
@endsection
