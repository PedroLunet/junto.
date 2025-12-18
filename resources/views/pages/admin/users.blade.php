@extends('layouts.admin')

@section('page-title', 'Users')

@section('content')
    <div class="mx-20 my-10 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
            <p class="text-gray-600 mt-2 text-base">View and manage user accounts on the platform</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- Selection Info -->
            <div class="flex items-center space-x-2 text-base text-gray-600" id="selection-info" style="display: none;">
                <i class="fas fa-check"></i>
                <span id="selection-count">0 Selected</span>
            </div>

            <!-- Search Bar -->
            <x-ui.search-bar id="searchUser" placeholder="Search User" />

            <!-- Add User Button -->
            <x-ui.button variant="primary" onclick="openAddUserModal()" class="flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Add User</span>
            </x-ui.button>
        </div>
    </div>

    <div class="mx-20 space-y-6">
        <!-- Users Table -->
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
                                <i class="fas fa-caret-down text-sm sort-icon" data-direction="none"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="username">
                                <span>Username</span>
                                <i class="fas fa-caret-down text-sm sort-icon" data-direction="none"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="email">
                                <span>Email</span>
                                <i class="fas fa-caret-down text-sm sort-icon" data-direction="none"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="date">
                                <span>Joined</span>
                                <i class="fas fa-caret-down text-sm sort-icon" data-direction="none"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-2 hover:text-gray-700 sort-btn" data-column="status">
                                <span>Status</span>
                                <i class="fas fa-caret-down text-sm sort-icon" data-direction="none"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-base font-medium text-gray-500 uppercase tracking-wider">Edit
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
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-base font-medium
                        {{ $user->isblocked ? 'bg-red-100 text-red-800' : ($user->isadmin ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $user->isblocked ? 'Blocked' : ($user->isadmin ? 'Admin' : 'Active') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-base font-medium">
                                <div class="flex space-x-6">
                                    <button class="edit-user-btn text-blue-600 hover:text-blue-900"
                                        data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                        data-user-username="{{ $user->username }}" data-user-email="{{ $user->email }}"
                                        data-user-bio="{{ $user->bio }}"
                                        data-user-isadmin="{{ $user->isadmin ? 'true' : 'false' }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
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
    </div>

    <!-- Include Add User Modal -->
    <x-admin.add-user-modal />

    <!-- Include Edit User Modal -->
    <x-admin.edit-user-modal />
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            //=== CHECKBOXES ===
            const selectAllCheckbox = document.getElementById('select-all');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            const selectionInfo = document.getElementById('selection-info');
            const selectionCount = document.getElementById('selection-count');

            function updateSelectionCount() {
                const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
                const count = checkedBoxes.length;

                if (count > 0) {
                    selectionInfo.style.display = 'flex';
                    selectionCount.textContent = count + ' Selected';
                } else {
                    selectionInfo.style.display = 'none';
                }

                // update select all checkbox state
                if (count === userCheckboxes.length && userCheckboxes.length > 0) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (count > 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
            }

            // handle select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectionCount();
            });

            // handle individual checkboxes
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectionCount);
            });

            // start count
            updateSelectionCount();


            //=== SEARCH ===
            const searchInput = document.getElementById('searchUser');
            const userRows = document.querySelectorAll('tbody tr');

            function filterUsers() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                userRows.forEach(row => {
                    // skip the "No users found" row
                    if (row.querySelector('td[colspan]')) {
                        return;
                    }

                    const name = row.querySelector('td:nth-child(2) div').textContent.toLowerCase();
                    const username = row.querySelector('td:nth-child(3) div').textContent.toLowerCase();
                    const email = row.querySelector('td:nth-child(4) div').textContent.toLowerCase();

                    const matches = name.includes(searchTerm) ||
                        username.includes(searchTerm) ||
                        email.includes(searchTerm);

                    if (matches) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                        // uncheck hidden rows
                        const checkbox = row.querySelector('.user-checkbox');
                        if (checkbox && checkbox.checked) {
                            checkbox.checked = false;
                        }
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

            // add event listener for search input
            searchInput.addEventListener('input', filterUsers);

            // clear search
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    filterUsers();
                }
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
                // reset all icons
                document.querySelectorAll('.sort-icon').forEach(icon => {
                    icon.className = 'fas fa-caret-down text-sm sort-icon';
                    icon.setAttribute('data-direction', 'none');
                });

                // update active column icon
                const activeIcon = document.querySelector(`[data-column="${activeColumn}"] .sort-icon`);
                if (activeIcon) {
                    if (direction === 'asc') {
                        activeIcon.className = 'fas fa-caret-up text-sm sort-icon';
                    } else if (direction === 'desc') {
                        activeIcon.className = 'fas fa-caret-down text-sm sort-icon';
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
                    openEditUserModal(userData);
                });
            });
        });
    </script>
@endpush
