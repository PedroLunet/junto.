@extends('layouts.admin')

@section('content')
    {{-- Server-side alert (if any) --}}
    @if (session('alert'))
        <x-ui.alert-card :type="session('alert.type', 'success')" :title="session('alert.title', '')" :message="session('alert.message', '')" :dismissible="true" />
    @endif
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div
                class="mx-2 md:mx-6 lg:mx-20 mt-6 md:mt-10 mb-4 flex flex-col md:flex-col lg:flex-row md:gap-4 lg:gap-0 md:items-stretch lg:items-center md:justify-start lg:justify-between">
                <div class="flex flex-col items-start md:order-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manage Users</h1>
                    <p class="text-gray-600 mt-1 md:mt-2 text-sm md:text-base">View and manage user accounts on the platform
                    </p>
                </div>
                <div
                    class="flex flex-col md:flex-row items-stretch md:items-center gap-2 md:gap-4 w-full md:w-auto md:order-2 mt-4 md:mt-6 lg:mt-0">
                    <!-- Selection Info -->
                    <div class="flex items-center space-x-2 text-base text-gray-600 order-3 sm:order-1" id="selection-info"
                        style="display: none;">
                        <i class="fas fa-check"></i>
                        <span id="selection-count">0 Selected</span>
                    </div>

                    <!-- Search Bar: Desktop (table) -->
                    <div class="order-1 sm:order-2 w-full sm:w-auto hidden lg:block">
                        <x-ui.search-bar id="searchUserTable" placeholder="Search User" class="w-full sm:w-52" />
                    </div>

                    <!-- Search Bar & Sort: Mobile & Tablet (card list) -->
                    <div class="order-1 sm:order-2 w-full sm:w-auto flex flex-col md:flex-row gap-2 md:gap-4 lg:hidden">
                        <x-ui.search-bar id="searchUserList" placeholder="Search User" class="w-full sm:w-52 md:w-64" />
                        <x-ui.sort-dropdown :options="[
                            'name' => 'Name',
                            'username' => 'Username',
                            'email' => 'Email',
                            'date' => 'Joined',
                        ]" defaultValue="name" onSort="sortUserCards"
                            onToggleOrder="toggleUserCardsOrder" class="md:w-40" />
                    </div>

                    <!-- Add User Button -->
                    <div class="order-2 sm:order-3 w-full sm:w-auto">
                        <x-ui.button variant="primary" onclick="openAddUserModal()"
                            class="flex items-center justify-center space-x-2 w-full sm:w-auto">
                            <i class="fas fa-plus"></i>
                            <span>Add User</span>
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="mx-2 md:mx-6 lg:mx-20 my-6">
                <!-- desktop: table (only on large screens and up) -->
                <div class="hidden lg:block">
                    <x-admin.users.users-table :users="$users" />
                </div>
                <!-- mobile & tablet: list of cards (shown up to md and lg) -->
                <div class="block lg:hidden">
                    <x-admin.users.users-list :users="$users" />
                </div>
            </div>
        </div>
    </div>

    <x-admin.users.add-user-modal />
    <x-admin.users.edit-user-modal />
    <x-admin.users.delete-user-modal />

    <x-ui.confirm />
@endsection
