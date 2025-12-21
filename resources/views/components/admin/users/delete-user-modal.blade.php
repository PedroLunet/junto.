<div id="adminDeleteUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div
        class="bg-white rounded-lg w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl mx-2 sm:mx-4 p-2 sm:p-4 md:p-6">
        <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 mb-2 sm:mb-4">Delete User<span
                id="deleteUserPlural"></span></h3>
        <p class="text-gray-600 mb-2 sm:mb-4 text-sm sm:text-base">This action cannot be undone. Enter your password to
            confirm deletion of <span id="deleteUserNames" class="font-bold"></span>.</p>
        <x-ui.input type="password" id="adminDeleteUserPassword" name="adminDeleteUserPassword"
            placeholder="Enter your password" class="mb-2 sm:mb-4" label="" />
        <div class="flex gap-2 sm:gap-4">
            <x-ui.button type="button" variant="secondary" class="flex-1 text-base sm:text-xl"
                onclick="closeAdminDeleteUserModal()">Cancel</x-ui.button>
            <x-ui.button type="button" variant="danger" class="flex-1 text-base sm:text-xl"
                onclick="confirmAdminDeleteUser()">Delete</x-ui.button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let deleteUserIds = [];
        let deleteUserNames = [];

        window.openAdminDeleteUserModal = function(userIds, userNames) {
            deleteUserIds = userIds;
            deleteUserNames = userNames;
            document.getElementById('deleteUserNames').textContent = userNames.join(' and ');
            document.getElementById('deleteUserPlural').textContent = userNames.length > 1 ? 's' : '';
            document.getElementById('adminDeleteUserPassword').value = '';
            document.getElementById('adminDeleteUserModal').classList.remove('hidden');
        }

        window.closeAdminDeleteUserModal = function() {
            document.getElementById('adminDeleteUserModal').classList.add('hidden');
            deleteUserIds = [];
            deleteUserNames = [];
        }

        window.confirmAdminDeleteUser = async function() {
            const password = document.getElementById('adminDeleteUserPassword').value;
            if (!password) {
                alert('Please enter your password');
                return;
            }
            if (!deleteUserIds.length) return;
            let allSuccess = true;
            let errorMessages = [];
            for (let i = 0; i < deleteUserIds.length; i++) {
                try {
                    const response = await fetch(`/admin/users/${deleteUserIds[i]}/delete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            password
                        })
                    });
                    const data = await response.json();
                    if (!data.success) {
                        allSuccess = false;
                        errorMessages.push(data.message || `Failed to delete ${deleteUserNames[i]}`);
                    }
                } catch (error) {
                    allSuccess = false;
                    errorMessages.push(
                        `An error occurred while trying to delete ${deleteUserNames[i]}.`);
                }
            }
            if (allSuccess) {
                alert(
                    `User${deleteUserNames.length > 1 ? 's' : ''} ${deleteUserNames.join(' and ')} ${deleteUserNames.length > 1 ? 'have' : 'has'} been deleted successfully.`
                );
                window.location.reload();
            } else {
                alert(errorMessages.join('\n'));
                window.closeAdminDeleteUserModal();
            }
        }

        // Attach event listeners to delete buttons
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Get all selected users (table)
                const checkedBoxes = Array.from(document.querySelectorAll(
                    '.user-checkbox:checked'));
                let selectedIds = [];
                let selectedNames = [];
                if (checkedBoxes.length > 0) {
                    checkedBoxes.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        const nameCell = row ? row.querySelector(
                            'td:nth-child(2) div') : null;
                        selectedIds.push(checkbox.value);
                        selectedNames.push(nameCell ? nameCell.textContent.trim() : '');
                    });
                } else if (this.dataset.userId && this.closest('.user-card')) {
                    // Card view (mobile/tablet)
                    const userId = this.dataset.userId;
                    const userCard = this.closest('.user-card');
                    // Try to get the name from the card
                    let userName = '';
                    const nameEl = userCard ? userCard.querySelector('h3') : null;
                    if (nameEl) userName = nameEl.textContent.trim();
                    selectedIds = [userId];
                    selectedNames = [userName];
                } else {
                    // fallback to single user if none selected (table)
                    const userRow = this.closest('tr');
                    if (userRow) {
                        const userId = userRow.querySelector('.user-checkbox').value;
                        const nameCell = userRow.querySelector('td:nth-child(2) div');
                        const userName = nameCell ? nameCell.textContent.trim() : '';
                        selectedIds = [userId];
                        selectedNames = [userName];
                    }
                }
                openAdminDeleteUserModal(selectedIds, selectedNames);
            });
        });

        // Allow closing modal with Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !document.getElementById('adminDeleteUserModal').classList
                .contains('hidden')) {
                closeAdminDeleteUserModal();
            }
        });
    });
</script>
