@extends('layouts.app')

@section('content')
    <!-- Blocked Modal Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 md:p-12 text-center">
                <!-- Icon -->
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

                <p class="text-base sm:text-base md:text-lg text-gray-500 mb-8 sm:mb-12">
                    If you believe this is a mistake, please appeal for unblock.
                </p>


                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <x-ui.button variant="secondary" onclick="appealUnblock()"
                        class="text-lg sm:text-xl md:text-xl px-8 sm:px-10 md:px-12 py-3 sm:py-4 w-full sm:w-auto">
                        <i class="fas fa-envelope mr-2 sm:mr-3"></i>
                        Appeal for Unblock
                    </x-ui.button>
                    <x-ui.button variant="primary" onclick="window.location.href='{{ route('logout') }}'"
                        class="text-lg sm:text-xl md:text-xl px-8 sm:px-10 md:px-12 py-3 sm:py-4 w-full sm:w-auto">
                        <i class="fas fa-sign-out-alt mr-2 sm:mr-3"></i>
                        Logout
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <!-- Appeal Modal -->
    <div id="appealModal" class="fixed inset-0 bg-black bg-opacity-60 z-60 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 max-w-lg w-full">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Appeal for Unblock</h2>
            <p class="text-gray-600 mb-6">Please explain why you believe your account should be unblocked.</p>

            <!-- Alert Container -->
            <div id="appealAlert" class="hidden mb-4"></div>

            <form id="appealForm">
                @csrf
                <div class="mb-6">
                    <label for="appealReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Appeal <span class="text-red-500">*</span>
                    </label>
                    <textarea id="appealReason" name="reason" rows="5" required maxlength="1000"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#a17f8f] focus:border-transparent resize-none"
                        placeholder="Explain your situation..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeAppealModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" id="submitAppealBtn"
                        class="flex-1 px-4 py-2 bg-[#a17f8f] text-white rounded-lg hover:bg-[#7a5466] transition">
                        Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAppealAlert(message, type = 'success') {
            const alertContainer = document.getElementById('appealAlert');
            const isSuccess = type === 'success';
            const bgColor = isSuccess ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
            const iconColor = isSuccess ? 'text-green-600' : 'text-red-600';
            const icon = isSuccess ?
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';

            alertContainer.innerHTML = `
                <div class="flex items-start gap-3 px-4 py-3 rounded-lg border ${bgColor}">
                    <div class="shrink-0 ${iconColor}">${icon}</div>
                    <div class="flex-1 text-sm text-gray-700">${message}</div>
                    <button type="button" onclick="this.closest('div').parentElement.classList.add('hidden')" class="shrink-0 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            `;
            alertContainer.classList.remove('hidden');
        }

        async function appealUnblock() {
            document.getElementById('appealModal').classList.remove('hidden');
            document.getElementById('appealModal').classList.add('flex');
        }

        function closeAppealModal() {
            document.getElementById('appealModal').classList.add('hidden');
            document.getElementById('appealModal').classList.remove('flex');
            document.getElementById('appealForm').reset();
            document.getElementById('appealAlert').classList.add('hidden');
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
                    showAppealAlert(
                        'Your appeal has been submitted successfully. Administrators will review it shortly.',
                        'success');
                    setTimeout(() => {
                        closeAppealModal();
                    }, 2000);
                } else {
                    showAppealAlert(data.message || 'Failed to submit appeal. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
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
