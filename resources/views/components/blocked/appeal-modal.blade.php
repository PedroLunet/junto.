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
