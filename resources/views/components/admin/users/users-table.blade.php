<div class="bg-white overflow-hidden rounded-xl">
    <table class="min-w-full border-collapse">
        <thead class="bg-gray-50">
            <tr>
                <th class="pl-6 pr-3 py-3 text-left w-14">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300">
                </th>
                <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                    <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="name">
                        <span>Name</span>
                        <i class="fas fa-caret-down text-lg sort-icon text-gray-400" data-direction="none"></i>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                    <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="username">
                        <span>Username</span>
                        <i class="fas fa-caret-down text-lg sort-icon text-gray-400" data-direction="none"></i>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                    <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="email">
                        <span>Email</span>
                        <i class="fas fa-caret-down text-lg sort-icon text-gray-400" data-direction="none"></i>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                    <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="date">
                        <span>Joined</span>
                        <i class="fas fa-caret-down text-lg sort-icon text-gray-400" data-direction="none"></i>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                    <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="status">
                        <span>Status</span>
                        <i class="fas fa-caret-down text-lg sort-icon text-gray-400" data-direction="none"></i>
                    </button>
                </th>
                <th class="px-3 py-3 text-center text-base font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="pl-6 pr-3 py-4">
                        <input type="checkbox" class="user-checkbox rounded border-gray-300"
                            value="{{ $user->id }}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-base font-medium text-gray-900">{{ $user->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-base text-gray-900">{{ $user->username }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-base text-gray-900">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-base text-gray-900">
                            {{ $user->createdat ? \Carbon\Carbon::parse($user->createdat)->format('M d, Y') : 'N/A' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-base font-medium {{ $user->isblocked ? 'bg-red-100 text-red-800' : ($user->isadmin ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                            {{ $user->isblocked ? 'Blocked' : ($user->isadmin ? 'Admin' : 'Active') }}
                        </span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-base font-medium">
                        <div class="flex justify-center space-x-1">
                            <x-ui.icon-button variant="blue" class="edit-user-btn" data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}" data-user-username="{{ $user->username }}"
                                data-user-email="{{ $user->email }}" data-user-bio="{{ $user->bio }}"
                                data-user-isadmin="{{ $user->isadmin ? 'true' : 'false' }}">
                                <i class="fas fa-edit"></i>
                            </x-ui.icon-button>
                            <x-ui.icon-button variant="yellow" class="ban-user-btn" data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-blocked="{{ $user->isblocked ? 'true' : 'false' }}">
                                <i class="fas fa-ban"></i>
                            </x-ui.icon-button>
                            <x-ui.icon-button variant="red" class="delete-user-btn">
                                <i class="fas fa-trash"></i>
                            </x-ui.icon-button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.querySelector('.users-table-component') || document.querySelector('table');
        if (!table) return;

        //=== CHECKBOXES ===
        const selectAllCheckbox = document.getElementById('select-all');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const selectionInfo = document.getElementById('selection-info');
        const selectionCount = document.getElementById('selection-count');

        function updateSelectionCount() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const count = checkedBoxes.length;
            if (selectionInfo && selectionCount) {
                if (count > 0) {
                    selectionInfo.style.display = 'flex';
                    selectionCount.textContent = count + ' Selected';
                } else {
                    selectionInfo.style.display = 'none';
                }
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateSelectionCount();
            });
        }

        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = Array.from(userCheckboxes).filter(cb => cb.checked).length;
                if (selectAllCheckbox) {
                    if (checkedCount === userCheckboxes.length && userCheckboxes.length > 0) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCount > 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                }
                updateSelectionCount();
            });
        });

        //=== SORTING ===
        const sortButtons = document.querySelectorAll('.sort-btn');
        let currentSort = {
            column: null,
            direction: 'none'
        };

        function sortTable(column, direction) {
            const tbody = document.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            const noUsersRow = tbody.querySelector('tr td[colspan]')?.parentElement;
            const dataRows = rows.filter(row => !row.querySelector('td[colspan]'));
            if (dataRows.length === 0) return;
            dataRows.sort((a, b) => {
                let aValue, bValue;
                switch (column) {
                    case 'name':
                        aValue = a.querySelector('td:nth-child(2) div').textContent.toLowerCase();
                        bValue = b.querySelector('td:nth-child(2) div').textContent.toLowerCase();
                        break;
                    case 'username':
                        aValue = a.querySelector('td:nth-child(3) div').textContent.toLowerCase();
                        bValue = b.querySelector('td:nth-child(3) div').textContent.toLowerCase();
                        break;
                    case 'email':
                        aValue = a.querySelector('td:nth-child(4) div').textContent.toLowerCase();
                        bValue = b.querySelector('td:nth-child(4) div').textContent.toLowerCase();
                        break;
                    case 'date':
                        aValue = a.querySelector('td:nth-child(5) div').textContent;
                        bValue = b.querySelector('td:nth-child(5) div').textContent;
                        if (aValue === 'N/A') aValue = '1900-01-01';
                        if (bValue === 'N/A') bValue = '1900-01-01';
                        aValue = new Date(aValue);
                        bValue = new Date(bValue);
                        break;
                    case 'status':
                        const statusOrder = {
                            'Admin': 0,
                            'Active': 1,
                            'Blocked': 2
                        };
                        const aStatusText = a.querySelector('td:nth-child(6) span').textContent.trim();
                        const bStatusText = b.querySelector('td:nth-child(6) span').textContent.trim();
                        aValue = statusOrder[aStatusText] !== undefined ? statusOrder[aStatusText] : 3;
                        bValue = statusOrder[bStatusText] !== undefined ? statusOrder[bStatusText] : 3;
                        break;
                }
                if (column === 'date' || column === 'status') {
                    return direction === 'asc' ? aValue - bValue : bValue - aValue;
                } else {
                    if (aValue < bValue) return direction === 'asc' ? -1 : 1;
                    if (aValue > bValue) return direction === 'asc' ? 1 : -1;
                    return 0;
                }
            });
            tbody.innerHTML = '';
            dataRows.forEach(row => tbody.appendChild(row));
            if (noUsersRow) tbody.appendChild(noUsersRow);
        }

        function updateSortIcons(activeColumn, direction) {
            document.querySelectorAll('.sort-btn').forEach(btn => {
                btn.classList.remove('text-gray-600', 'font-bold');
            });
            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.className = 'fas fa-caret-down text-lg sort-icon text-gray-400';
                icon.setAttribute('data-direction', 'none');
            });
            const activeBtn = document.querySelector(`[data-column="${activeColumn}"]`);
            if (activeBtn) activeBtn.classList.add('text-gray-600', 'font-bold');
            const activeIcon = document.querySelector(`[data-column="${activeColumn}"] .sort-icon`);
            if (activeIcon) {
                if (direction === 'asc') {
                    activeIcon.className = 'fas fa-caret-up text-xl sort-icon text-gray-600';
                } else if (direction === 'desc') {
                    activeIcon.className = 'fas fa-caret-down text-xl sort-icon text-gray-600';
                }
                activeIcon.setAttribute('data-direction', direction);
            }
        }

        sortButtons.forEach(button => {
            button.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                const icon = this.querySelector('.sort-icon');
                const currentDirection = icon.getAttribute('data-direction');
                let newDirection = (currentDirection === 'none' || currentDirection ===
                    'desc') ? 'asc' : 'desc';
                currentSort = {
                    column,
                    direction: newDirection
                };
                sortTable(column, newDirection);
                updateSortIcons(column, newDirection);
                updateSelectionCount();
            });
        });

        //=== EDIT USER ===
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const userData = {
                    id: this.getAttribute('data-user-id'),
                    name: this.getAttribute('data-user-name'),
                    username: this.getAttribute('data-user-username'),
                    email: this.getAttribute('data-user-email'),
                    bio: this.getAttribute('data-user-bio'),
                    isadmin: this.getAttribute('data-user-isadmin') === 'true'
                };
                if (typeof openEditUserModal === 'function') {
                    openEditUserModal(userData);
                }
            });
        });

        //=== DELETE USER ===
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const checkedBoxes = Array.from(document.querySelectorAll(
                    '.user-checkbox:checked'));
                let selectedIds = [];
                let selectedNames = [];
                if (checkedBoxes.length > 0) {
                    checkedBoxes.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        const nameCell = row.querySelector('td:nth-child(2) div');
                        selectedIds.push(checkbox.value);
                        selectedNames.push(nameCell ? nameCell.textContent.trim() : '');
                    });
                } else {
                    const userRow = this.closest('tr');
                    const userId = userRow.querySelector('.user-checkbox').value;
                    const nameCell = userRow.querySelector('td:nth-child(2) div');
                    const userName = nameCell ? nameCell.textContent.trim() : '';
                    selectedIds = [userId];
                    selectedNames = [userName];
                }
                if (typeof openAdminDeleteUserModal === 'function') {
                    openAdminDeleteUserModal(selectedIds, selectedNames);
                }
            });
        });

        //=== BAN/UNBAN USER ===
        document.querySelectorAll('.ban-user-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const checkedBoxes = Array.from(document.querySelectorAll(
                    '.user-checkbox:checked'));
                let selectedUsers = [];
                if (checkedBoxes.length > 0) {
                    selectedUsers = checkedBoxes.map(checkbox => {
                        const row = checkbox.closest('tr');
                        const nameCell = row.querySelector('td:nth-child(2) div');
                        return {
                            id: checkbox.value,
                            name: nameCell ? nameCell.textContent.trim() : ''
                        };
                    });
                } else {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    selectedUsers = [{
                        id: userId,
                        name: userName
                    }];
                }
                const isBlocked = this.getAttribute('data-user-blocked') === 'true';
                const action = isBlocked ? 'unblock' : 'block';
                const actionText = isBlocked ? 'Unblock' : 'Block';
                const namesList = selectedUsers.map(u => u.name).join(' and ');
                const confirmText = `Are you sure you want to ${action} ${namesList}?`;
                if (typeof alertConfirm !== 'function' || typeof alertInfo !== 'function')
                    return;
                const confirmed = await alertConfirm(confirmText,
                    `${actionText} User${selectedUsers.length > 1 ? 's' : ''}`);
                if (confirmed) {
                    let allSuccess = true;
                    let errorMessages = [];
                    for (const user of selectedUsers) {
                        try {
                            const response = await fetch(
                                `/admin/users/${user.id}/${action}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute(
                                            'content'),
                                        'Accept': 'application/json'
                                    }
                                });
                            const data = await response.json();
                            if (!data.success) {
                                allSuccess = false;
                                errorMessages.push(data.message ||
                                    `Failed to ${action} ${user.name}`);
                            }
                        } catch (error) {
                            allSuccess = false;
                            errorMessages.push(
                                `An error occurred while trying to ${action} ${user.name}.`
                            );
                        }
                    }
                    if (allSuccess) {
                        await alertInfo(
                            `User${selectedUsers.length > 1 ? 's' : ''} ${namesList} ${selectedUsers.length > 1 ? 'have' : 'has'} been ${action}ed successfully.`
                        );
                        window.location.reload();
                    } else {
                        await alertInfo(errorMessages.join('\n'));
                    }
                }
            });
        });

        // Initial selection count update
        updateSelectionCount();
    });
</script>
