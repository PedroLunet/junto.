@extends('layouts.admin')

@section('page-title', 'Users')

@section('content')
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div class="mx-20 mt-10 mb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
                    <p class="text-gray-600 mt-2 text-base">View and manage user accounts on the platform</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Selection Info -->
                    <div class="flex items-center space-x-2 text-base text-gray-600" id="selection-info"
                        style="display: none;">
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
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="mx-20 my-6">
                <x-admin.users.users-table :users="$users" />
            </div>
        </div>
    </div>

    <x-admin.users.add-user-modal />
    <x-admin.users.edit-user-modal />
    <x-admin.users.delete-user-modal />

    <x-ui.confirm />
@endsection