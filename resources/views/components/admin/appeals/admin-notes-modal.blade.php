<div id="adminNotesModal" class="fixed inset-0 z-50 hidden" onclick="closeAdminNotesModal()">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-6">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-2xl p-8"
                onclick="event.stopPropagation()">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Reject Appeal</h2>
                    <x-ui.icon-button onclick="closeAdminNotesModal()" variant="gray" aria-label="Close">
                        <i class="fas fa-times text-2xl"></i>
                    </x-ui.icon-button>
                </div>

                <p class="text-gray-600 mb-6 text-lg">
                    Add a note explaining why this appeal was rejected. This note will
                    be stored for administrative records.
                </p>

                <div class="mb-6">
                    <label for="adminNotesTextarea" class="block text-sm font-semibold text-gray-700 mb-2">
                        Admin Notes (Optional)
                    </label>
                    <textarea id="adminNotesTextarea" placeholder="Explain why this appeal was rejected..."
                        class="w-full min-h-[150px] p-4 border border-gray-300 rounded-lg focus:border-[#38157a] focus:ring-2 focus:ring-[#38157a] focus:ring-opacity-20 resize-none transition-colors"
                        maxlength="1000"></textarea>
                    <p class="text-sm text-gray-500 mt-2">
                        <span id="notesCharCount">0</span>/1000 characters
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <x-ui.button onclick="closeAdminNotesModal()" variant="secondary" class="text-xl px-8 py-3">
                        Cancel
                    </x-ui.button>
                    <x-ui.button onclick="submitRejection()" variant="danger" class="text-xl px-8 py-3">
                        <i class="fas fa-times-circle mr-2"></i>
                        Reject Appeal
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAppealId = null;

    function openAdminNotesModal(appealId) {
        currentAppealId = appealId;
        const modal = document.getElementById('adminNotesModal');
        const textarea = document.getElementById('adminNotesTextarea');

        modal.classList.remove('hidden');
        textarea.value = '';
        updateCharCount();

        // Focus on textarea
        setTimeout(() => textarea.focus(), 100);
    }

    function closeAdminNotesModal() {
        const modal = document.getElementById('adminNotesModal');
        modal.classList.add('hidden');
        currentAppealId = null;
    }

    function updateCharCount() {
        const textarea = document.getElementById('adminNotesTextarea');
        const charCount = document.getElementById('notesCharCount');
        charCount.textContent = textarea.value.length;
    }

    // Character counter
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('adminNotesTextarea');
        if (textarea) {
            textarea.addEventListener('input', updateCharCount);
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('adminNotesModal');
                if (!modal.classList.contains('hidden')) {
                    closeAdminNotesModal();
                }
            }
        });
    });

    function submitRejection() {
        if (!currentAppealId) {
            alertInfo('No appeal selected', 'Error');
            return;
        }

        const adminNotes = document.getElementById('adminNotesTextarea').value.trim();

        fetch(`/admin/appeals/${currentAppealId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    adminNotes: adminNotes || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAdminNotesModal();
                    window.location.reload();
                } else {
                    alertInfo(data.message || 'Failed to reject appeal', 'Error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertInfo('An error occurred while rejecting the appeal', 'Error');
            });
    }
</script>
