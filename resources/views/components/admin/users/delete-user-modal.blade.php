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
                showAlertCard(
                    'success',
                    'Success',
                    `User${deleteUserNames.length > 1 ? 's' : ''} ${deleteUserNames.join(' and ')} ${deleteUserNames.length > 1 ? 'have' : 'has'} been deleted successfully.`
                );
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlertCard('error', 'Error', errorMessages.join('\n'));
                window.closeAdminDeleteUserModal();
            }
            // === ALERT CARD ===
            function showAlertCard(type, title, message) {
                // Remove any existing alert card
                const existing = document.getElementById('js-dynamic-alert-card');
                if (existing) existing.remove();
                // Create wrapper div
                const wrapper = document.createElement('div');
                wrapper.id = 'js-dynamic-alert-card';
                wrapper.innerHTML = `
                        <div class='fixed top-6 right-6 z-50 flex items-start gap-3 px-4 py-4 rounded-2xl border ${type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'} shadow-sm mb-4 min-w-[280px] max-w-xs transition-all duration-300 ease-in-out opacity-0 translate-x-full'>
                            <div class='shrink-0 flex items-center justify-center w-8 h-8 rounded-full ${type === 'success' ? 'bg-green-200' : 'bg-red-200'}'>
                                ${type === 'success'
                                    ? `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke-width='2' class='stroke-green-400 fill-green-50'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4' class='stroke-green-600'/></svg>`
                                    : `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke-width='2' class='stroke-red-400 fill-red-50'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 9l-6 6m0-6l6 6' class='stroke-red-600'/></svg>`}
                            </div>
                            <div class='flex-1'>
                                <div class='font-semibold text-base ${type === 'success' ? 'text-green-600' : 'text-red-600'} mb-0.5'>${title}</div>
                                <div class='text-gray-700 text-sm'>${message}</div>
                            </div>
                            <button type='button' class='absolute top-2 right-2' onclick='this.closest("#js-dynamic-alert-card").remove()'>
                                <i class='fa fa-times w-5 h-5'></i>
                            </button>
                        </div>
                    `;
                document.body.appendChild(wrapper);
                // Animate in
                setTimeout(() => {
                    const alert = wrapper.firstElementChild;
                    alert.classList.remove('opacity-0', 'translate-x-full');
                    alert.classList.add('opacity-100', 'translate-x-0');
                }, 10);
                // Animate out after 5s
                setTimeout(() => {
                    const alert = wrapper.firstElementChild;
                    if (!alert) return;
                    alert.classList.remove('opacity-100', 'translate-x-0');
                    alert.classList.add('opacity-0', 'translate-x-full');
                    setTimeout(() => {
                        wrapper.remove();
                    }, 300);
                }, 5000);
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
