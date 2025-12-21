@extends('layouts.admin')

@section('page-title', 'Users')

@section('content')
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div
                class="mx-4 md:mx-20 mt-6 md:mt-10 mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-0">
                <div class="flex flex-col items-start">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manage Users</h1>
                    <p class="text-gray-600 mt-1 md:mt-2 text-sm md:text-base">View and manage user accounts on the platform
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4 w-full md:w-auto">
                    <!-- Selection Info -->
                    <div class="flex items-center space-x-2 text-base text-gray-600 order-3 sm:order-1" id="selection-info"
                        style="display: none;">
                        <i class="fas fa-check"></i>
                        <span id="selection-count">0 Selected</span>
                    </div>

                    <!-- Search Bar: Desktop (table) -->
                    <div class="order-1 sm:order-2 w-full sm:w-auto hidden md:block">
                        <x-ui.search-bar id="searchUserTable" placeholder="Search User" class="w-full sm:w-52" />
                    </div>

                    <!-- Search Bar: Mobile (card list) -->
                    <div class="order-1 sm:order-2 w-full sm:w-auto block md:hidden">
                        <x-ui.search-bar id="searchUserList" placeholder="Search User" class="w-full sm:w-52" />
                        <div class="mt-2 mb-4 flex justify-center">
                            <x-ui.sort-dropdown :options="[
                                'name' => 'Name',
                                'username' => 'Username',
                                'email' => 'Email',
                                'date' => 'Joined',
                            ]" defaultValue="name" onSort="sortUserCards"
                                onToggleOrder="toggleUserCardsOrder" />
                        </div>
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
            <div class="mx-2 md:mx-20 my-6">
                <!-- desktop: table -->
                <div class="hidden md:block">
                    <x-admin.users.users-table :users="$users" />
                </div>
                <!-- mobile: list of cards -->
                <div class="block md:hidden">
                    <x-admin.users.users-list :users="$users" />
                </div>
            </div>
        </div>
    </div>

    <x-admin.users.add-user-modal />
    <x-admin.users.edit-user-modal />
    <x-admin.users.delete-user-modal />

    <x-ui.confirm />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchUserList');
            if (!searchInput) return;
            const cardContainer = document.getElementById('user-cards-list');
            if (!cardContainer) return;

            function filterCards() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const cards = cardContainer.querySelectorAll('.user-card');
                let visibleCount = 0;
                cards.forEach(card => {
                    const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                    const username = card.querySelectorAll('span.text-gray-900')[0]?.textContent
                        .toLowerCase() || '';
                    const email = card.querySelectorAll('span.text-gray-900')[1]?.textContent
                    .toLowerCase() || '';
                    if (
                        name.includes(searchTerm) ||
                        username.includes(searchTerm) ||
                        email.includes(searchTerm)
                    ) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                // Show/hide empty state if present
                const emptyState = cardContainer.querySelector(
                'x-ui-empty-state, .empty-state, [data-empty-state]');
                let noResultsDiv = document.getElementById('no-results-card-list');
                if (visibleCount === 0 && searchTerm !== '') {
                    if (emptyState) emptyState.style.display = 'none';
                    if (!noResultsDiv) {
                        noResultsDiv = document.createElement('div');
                        noResultsDiv.id = 'no-results-card-list';
                        noResultsDiv.className =
                            'col-span-full py-10 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300 mt-4';
                        cardContainer.appendChild(noResultsDiv);
                    }
                    noResultsDiv.textContent = `No users found matching "${searchTerm}"`;
                    noResultsDiv.style.display = '';
                } else {
                    if (noResultsDiv) noResultsDiv.style.display = 'none';
                    if (emptyState) emptyState.style.display = visibleCount === 0 ? '' : 'none';
                }
            }
            searchInput.addEventListener('input', filterCards);
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    filterCards();
                }
            });
        });
    </script>
@endsection
