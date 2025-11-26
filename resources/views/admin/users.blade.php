@extends('layouts.admin')

@section('page-title', 'Users')

@section('content')
    <div class="space-y-6">
        <!-- header with search and add user -->
        <div class="flex justify-between items-center">
            <!-- search bar -->
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="Search User"
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 w-80">
                </div>
                <div class="flex items-center space-x-2 text-2xl text-gray-600" id="selection-info" style="display: none;">
                    <i class="fas fa-check"></i>
                    <span id="selection-count">0 Selected</span>
                </div>
            </div>

            <!-- Add User Button -->
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>ADD USER</span>
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300">
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
                            <td class="px-6 py-4">
                                <input type="checkbox" class="user-checkbox rounded border-gray-300"
                                    value="{{ $user->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-2xl font-medium text-gray-900">{{ $user->name }}</div>
                                    </div>
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
                                <div class="flex space-x-2">
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

                // Update select all checkbox state
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

            // Handle select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectionCount();
            });

            // Handle individual checkboxes
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectionCount);
            });

            // Initialize count
            updateSelectionCount();
        });
    </script>
@endpush
