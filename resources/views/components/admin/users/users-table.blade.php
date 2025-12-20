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

                // update select all checkbox state
                if (selectAllCheckbox) {
                    document.querySelectorAll('.delete-user-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            // Get all selected users
                            const checkedBoxes = Array.from(document.querySelectorAll(
                                '.user-checkbox:checked'));
                            let selectedIds = [];
                            let selectedNames = [];
                            if (checkedBoxes.length > 0) {
                                checkedBoxes.forEach(checkbox => {
                                    const row = checkbox.closest('tr');
                                    const nameCell = row.querySelector(
                                        'td:nth-child(2) div');
                                    selectedIds.push(checkbox.value);
                                    selectedNames.push(nameCell ? nameCell.textContent
                                        .trim() : '');
                                });
                            } else {
                                // fallback to single user if none selected
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
                    row.style.display = '';
                    visibleCount++;

                    // highlight matching text
                    if (searchTerm) {
                        const regex = new RegExp(
                            `(${searchTerm.replace(/[.*+?^${}()|[\\]\\]/g, '\\$&')})`, 'gi');

                        if (nameElement) {
                            if (name.includes(searchTerm)) {
                                nameElement.innerHTML = nameElement.dataset.original.replace(regex,
                                    '<span class="bg-yellow-200">$1</span>');
                            } else {
                                nameElement.textContent = nameElement.dataset.original;
                            }
                        }
                        if (usernameElement) {
                            if (username.includes(searchTerm)) {
                                usernameElement.innerHTML = usernameElement.dataset.original.replace(
                                    regex,
                                    '<span class="bg-yellow-200">$1</span>');
                            } else {
                                usernameElement.textContent = usernameElement.dataset.original;
                            }
                        }
                        if (emailElement) {
                            if (email.includes(searchTerm)) {
                                emailElement.innerHTML = emailElement.dataset.original.replace(regex,
                                    '<span class="bg-yellow-200">$1</span>');
                            } else {
                                emailElement.textContent = emailElement.dataset.original;
                            }
                        }
                    } else {
                        // reset to original text when no search term
                        if (nameElement) nameElement.textContent = nameElement.dataset.original;
                        if (usernameElement) usernameElement.textContent = usernameElement.dataset
                            .original;
                        if (emailElement) emailElement.textContent = emailElement.dataset.original;
                    }
                } else {
                    row.style.display = 'none';
                    // uncheck hidden rows
                    const checkbox = row.querySelector('.user-checkbox');
                    if (checkbox && checkbox.checked) {
                        checkbox.checked = false;
                    }
                    // Reset to original text for hidden rows
                    if (nameElement) nameElement.textContent = nameElement.dataset.original;
                    if (usernameElement) usernameElement.textContent = usernameElement.dataset.original;
                    if (emailElement) emailElement.textContent = emailElement.dataset.original;
                }
            });

        // update selection count after filtering
        updateSelectionCount();

        // show/hide "No users found" message
        const noUsersRow = document.querySelector('tbody tr td[colspan]');
        if (noUsersRow) {
            const noUsersRowElement = noUsersRow.parentElement;
            if (visibleCount === 0 && searchTerm !== '') {
                // show custom "No results found" message
                noUsersRow.textContent = `No users found matching "${searchTerm}"`;
                noUsersRowElement.style.display = '';
            } else if (visibleCount === 0 && searchTerm === '') {
                // show original "No users found" message
                noUsersRow.textContent = 'No users found.';
                noUsersRowElement.style.display = '';
            } else {
                // hide the message when there are results
                noUsersRowElement.style.display = 'none';
            }
        } else if (visibleCount === 0 && searchTerm !== '') {
            // create and insert "No results found" message if it doesn't exist
            const tbody = document.querySelector('tbody');
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML =
                `<td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found matching "${searchTerm}"</td>`;
            noResultsRow.id = 'no-results-row';
            tbody.appendChild(noResultsRow);
        } else {
            // remove any dynamically created no results row
            const dynamicNoResultsRow = document.getElementById('no-results-row');
            if (dynamicNoResultsRow) {
                dynamicNoResultsRow.remove();
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterUsers);
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterUsers();
            }
        });
    }

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

        // remove no users row from sorting
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
                    // Handle N/A dates
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

            if (column === 'date') {
                return direction === 'asc' ? aValue - bValue : bValue - aValue;
            } else if (column === 'status') {
                return direction === 'asc' ? aValue - bValue : bValue - aValue;
            } else {
                if (aValue < bValue) return direction === 'asc' ? -1 : 1;
                if (aValue > bValue) return direction === 'asc' ? 1 : -1;
                return 0;
            }
        });

        // clear tbody and re-append sorted rows
        tbody.innerHTML = '';
        dataRows.forEach(row => tbody.appendChild(row));

        // add back no users row if it exists
        if (noUsersRow) {
            tbody.appendChild(noUsersRow);
        }
    }

    function updateSortIcons(activeColumn, direction) {
        // reset all icons and button text color
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.classList.remove('text-gray-600', 'font-bold');
        });
        document.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-caret-down text-lg sort-icon text-gray-400';
            icon.setAttribute('data-direction', 'none');
        });

        // update active column icon and button
        const activeBtn = document.querySelector(`[data-column="${activeColumn}"]`);
        if (activeBtn) {
            activeBtn.classList.add('text-gray-600', 'font-bold');
        }
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

    // click listeners to sort buttons
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            const icon = this.querySelector('.sort-icon');
            const currentDirection = icon.getAttribute('data-direction');

            let newDirection;
            if (currentDirection === 'none' || currentDirection === 'desc') {
                newDirection = 'asc';
            } else {
                newDirection = 'desc';
            }

            currentSort = {
                column,
                direction: newDirection
            };
            sortTable(column, newDirection);
            updateSortIcons(column, newDirection);

            // update selection count after sorting
            updateSelectionCount();
        });
    });


    //=== EDIT USER ===
    // attach event listeners to edit buttons
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
        button.addEventListener('click', async function() {
            // Get all selected users
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
                // fallback to single user if none selected
                const userRow = this.closest('tr');
                const userId = userRow.querySelector('.user-checkbox').value;
                const nameCell = userRow.querySelector('td:nth-child(2) div');
                const userName = nameCell ? nameCell.textContent.trim() : '';
                selectedUsers = [{
                    id: userId,
                    name: userName
                }];
            }

            // Build confirmation text
            const namesList = selectedUsers.map(u => u.name).join(' and ');
            const confirmText =
                `Are you sure you want to delete ${namesList}? This action cannot be undone.`;

            if (typeof alertConfirm !== 'function' || typeof alertInfo !== 'function')
                return;
            const confirmed = await alertConfirm(confirmText,
                `Delete User${selectedUsers.length > 1 ? 's' : ''}`);

            if (confirmed) {
                let allSuccess = true;
                let errorMessages = [];
                for (const user of selectedUsers) {
                    try {
                        const response = await fetch(`/admin/users/${user.id}/delete`, {
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
                                `Failed to delete ${user.name}`);
                        }
                    } catch (error) {
                        allSuccess = false;
                        errorMessages.push(
                            `An error occurred while trying to delete ${user.name}.`
                        );
                    }
                }
                if (allSuccess) {
                    await alertInfo(
                        `User${selectedUsers.length > 1 ? 's' : ''} ${namesList} ${selectedUsers.length > 1 ? 'have' : 'has'} been deleted successfully.`
                    );
                    window.location.reload();
                } else {
                    await alertInfo(errorMessages.join('\n'));
                }
            }
        });
    });

    //=== BAN/UNBAN USER ===
    // attach event listeners to ban buttons
    document.querySelectorAll('.ban-user-btn').forEach(button => {
    button.addEventListener('click', async function() {
        // Get all selected users
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
            // fallback to single user if none selected
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            selectedUsers = [{
                id: userId,
                name: userName
            }];
        }

        // Determine action (block/unblock) based on the clicked button
        const isBlocked = this.getAttribute('data-user-blocked') === 'true';
        const action = isBlocked ? 'unblock' : 'block';
        const actionText = isBlocked ? 'Unblock' : 'Block';

        // Build confirmation text
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
    });
</script>
