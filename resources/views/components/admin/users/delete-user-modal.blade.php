<div id="adminDeleteUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Delete User</h3>
        <p class="text-gray-600 mb-4">This action cannot be undone. Enter your password to confirm deletion of <span
                id="deleteUserName" class="font-bold"></span>.</p>
        <x-ui.input type="password" id="adminDeleteUserPassword" name="adminDeleteUserPassword"
            placeholder="Enter your password" class="mb-4" label="" />
        <div class="flex gap-4">
            <x-ui.button type="button" variant="secondary" class="flex-1"
                onclick="closeAdminDeleteUserModal()">Cancel</x-ui.button>
            <x-ui.button type="button" variant="danger" class="flex-1"
                onclick="confirmAdminDeleteUser()">Delete</x-ui.button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let deleteUserId = null;
        window.openAdminDeleteUserModal = function(userId, userName) {
            deleteUserId = userId;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('adminDeleteUserPassword').value = '';
            document.getElementById('adminDeleteUserModal').classList.remove('hidden');
        }
        window.closeAdminDeleteUserModal = function() {
            document.getElementById('adminDeleteUserModal').classList.add('hidden');
            deleteUserId = null;
        }
        window.confirmAdminDeleteUser = async function() {
            const password = document.getElementById('adminDeleteUserPassword').value;
            if (!password) {
                alert('Please enter your password');
                return;
            }
            if (!deleteUserId) return;
            try {
                const response = await fetch(`/admin/users/${deleteUserId}/delete`, {
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
                if (data.success) {
                    alert('User deleted successfully.');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete user.');
                    window.closeAdminDeleteUserModal();
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                window.closeAdminDeleteUserModal();
            }
        }
        // Attach event listeners to delete buttons
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const userRow = this.closest('tr');
                const userId = userRow.querySelector('.user-checkbox').value;
                // try to get the name cell, fallback to username if not found
                let userName = '';
                const nameCell = userRow.querySelector('td:nth-child(2) div');
                if (nameCell && nameCell.textContent.trim()) {
                    userName = nameCell.textContent.trim();
                } else {
                    const usernameCell = userRow.querySelector('td:nth-child(3) div');
                    userName = usernameCell ? usernameCell.textContent.trim() : '';
                }
                openAdminDeleteUserModal(userId, userName);
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
