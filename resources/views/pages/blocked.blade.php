@extends('layouts.app')

@section('content')
    <!-- alert container -->
    <div id="appealAlert" class="fixed top-4 right-4 z-60 hidden" style="max-width: 400px;">
        <div id="successAlert" class="hidden">
            <x-ui.alert-card type="success" title="Success" message="" id="successAlertCard" />
        </div>
        <div id="errorAlert" class="hidden">
            <x-ui.alert-card type="error" title="Error" message="" id="errorAlertCard" />
        </div>
    </div>

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

                <h1 class="text-3xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">
                    Account Blocked
                </h1>

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
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            // Hide both alerts first
            successAlert.classList.add('hidden');
            errorAlert.classList.add('hidden');

            // Update message in the appropriate alert
            if (type === 'success') {
                const messageDiv = successAlert.querySelector('.text-gray-700');
                if (messageDiv) messageDiv.textContent = message;
                successAlert.classList.remove('hidden');
            } else {
                const messageDiv = errorAlert.querySelector('.text-gray-700');
                if (messageDiv) messageDiv.textContent = message;
                errorAlert.classList.remove('hidden');
            }

            // Show the container
            alertContainer.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertContainer.classList.add('hidden');
                successAlert.classList.add('hidden');
                errorAlert.classList.add('hidden');
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
