@extends('layouts.admin')

@section('page-title', 'Users')

@section('content')
    <div class="space-y-6">
        <!-- header with search and add user -->
        <div class="flex justify-between items-center">
            <!-- search bar -->
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" id="searchUser" placeholder="Search User"
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 w-80">
                </div>
                <div class="flex items-center space-x-2 text-2xl text-gray-600" id="selection-info" style="display: none;">
                    <i class="fas fa-check"></i>
                    <span id="selection-count">0 Selected</span>
                </div>
            </div>

            <!-- Add User Button -->
            <button onclick="openAddUserModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Add User</span>
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white overflow-hidden">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="pl-16 pr-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 ml-8">
                        </th>
                        <th class="px-6 py-3 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">Username
                        </th>
                        <th class="px-6 py-3 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">Email
                        </th>
                        <th class="px-6 py-3 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">Joined
                        </th>
                        <th class="px-6 py-3 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">Edit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="pl-16 pr-6 py-4">
                                <input type="checkbox" class="user-checkbox rounded border-gray-300 ml-8"
                                    value="{{ $user->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-2xl font-medium text-gray-900">{{ $user->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-2xl text-gray-900">{{ $user->username }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-2xl text-gray-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-2xl text-gray-900">
                                    {{ $user->createdat ? \Carbon\Carbon::parse($user->createdat)->format('M d, Y') : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xl font-medium
                        {{ $user->isblocked ? 'bg-red-100 text-red-800' : ($user->isadmin ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $user->isblocked ? 'Blocked' : ($user->isadmin ? 'Admin' : 'Active') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-2xl font-medium">
                                <div class="flex space-x-6">
                                    <button class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // search functionality
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
                }
            }

            // add event listener for search input
            searchInput.addEventListener('input', filterUsers);

            // clear search functionality 
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    filterUsers();
                }
            });
        });
    </script>
@endpush
