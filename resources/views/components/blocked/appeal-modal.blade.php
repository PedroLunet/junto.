<div id="appealModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 max-w-lg w-full">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Appeal for Unblock</h2>
        <p class="text-gray-600 mb-6">Please explain why you believe your account should be unblocked.</p>

        <form id="appealForm">
            @csrf
            <x-ui.input type="textarea" id="appealReason" name="reason" label="Reason for Appeal"
                placeholder="Explain your situation..." :required="true" rows="5" maxlength="1000" />

            <div class="flex gap-3">
                <x-ui.button type="button" onclick="closeAppealModal()" variant="secondary" class="flex-1">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" id="submitAppealBtn" variant="primary" class="flex-1">
                    Submit Appeal
                </x-ui.button>
            </div>
        </form>
    </div>
</div>

<script>
    // global flag to prevent double submission and modal reopening
    window.hasPendingAppeal = false;

    async function appealUnblock() {
        if (window.hasPendingAppeal) {
            showAppealAlert('You already have a pending appeal under review.', 'error');
            return;
        }
        document.getElementById('appealModal').classList.remove('hidden');
        document.getElementById('appealModal').classList.add('flex');
    }

    function closeAppealModal() {
        document.getElementById('appealModal').classList.add('hidden');
        document.getElementById('appealModal').classList.remove('flex');
        document.getElementById('appealForm').reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('appealForm');
        if (!form) return;

        // Remove any previous handler
        if (window._appealFormHandler) {
            form.removeEventListener('submit', window._appealFormHandler);
        }

        window._appealFormHandler = async function(e) {
            console.log('Appeal form submit handler called');
            e.preventDefault();

            const submitBtn = document.getElementById('submitAppealBtn');
            if (window.hasPendingAppeal || submitBtn.disabled) {
                showAppealAlert('You already have a pending appeal under review.', 'error');
                return;
            }

            const reason = document.getElementById('appealReason').value.trim();
            if (!reason) {
                showAppealAlert('Please provide a reason for your appeal.', 'error');
                return;
            }

            // Set pending flag and disable button only after validation
            window.hasPendingAppeal = true;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                const response = await fetch('{{ route('appeal.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
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
                    submitBtn.textContent = 'Submitted';
                    // optionally, disable the "Appeal for Unblock" button if accessible globally
                    const appealBtn = document.querySelector('[onclick*="appealUnblock"]');
                    if (appealBtn) appealBtn.disabled = true;
                } else {
                    window.hasPendingAppeal = false;
                    closeAppealModal();
                    showAppealAlert(data.message || 'Failed to submit appeal. Please try again.',
                        'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Appeal';
                }
            } catch (error) {
                window.hasPendingAppeal = false;
                console.error('Error:', error);
                closeAppealModal();
                showAppealAlert('An error occurred while submitting your appeal. Please try again.',
                    'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Appeal';
            }
        };
        form.addEventListener('submit', window._appealFormHandler);
    });

    document.getElementById('appealModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAppealModal();
        }
    });
</script>
