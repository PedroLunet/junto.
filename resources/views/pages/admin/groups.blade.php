@extends('layouts.admin')

@section('title', 'Groups')

@section('content')
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div class="mx-2 md:mx-6 lg:mx-20 mt-6 md:mt-10 mb-4 flex flex-col gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Moderate Groups</h1>
                    <p class="text-gray-600 mt-1 md:mt-2 text-sm md:text-base">Manage and moderate groups on the platform</p>
                </div>
                <div class="flex flex-col md:flex-row md:items-center w-full gap-4 md:gap-4 items-center justify-center">
                    <div class="flex flex-col lg:flex-row w-full gap-4 items-center justify-center">
                        <div class="flex flex-col sm:flex-row w-full gap-4 items-center justify-center">
                            <x-ui.search-bar id="searchGroups" placeholder="Search Groups" class="w-full sm:w-52 md:w-64" />
                            <x-ui.sort-dropdown :options="[
                                'created_date' => 'Created Date',
                                'members' => 'Members',
                                'posts' => 'Posts',
                                'name' => 'Name',
                            ]" defaultValue="created_date" onSort="sortGroups"
                                onToggleOrder="toggleGroupSortOrder" class="md:w-40" />
                        </div>
                        <div class="w-full flex justify-center lg:justify-end mt-2 lg:mt-0">
                            <x-ui.filter-tabs :filters="[
                                'all' => ['label' => 'All', 'onclick' => 'filterGroups(\'all\')'],
                                'public' => ['label' => 'Public', 'onclick' => 'filterGroups(\'public\')'],
                                'private' => ['label' => 'Private', 'onclick' => 'filterGroups(\'private\')'],
                            ]" activeFilter="all" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div id="groups-container" class="mx-2 md:mx-6 lg:mx-20 my-6">
                <x-admin.groups.groups-list :groups="$groups" />
            </div>
        </div>
    </div>

    <x-ui.confirm />

    <script>
        let currentFilter = 'all';
        let currentSort = 'created_date';
        let sortAscending = false;

        // Search functionality
        const searchInput = document.getElementById('searchGroups');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                applyGroupFilterAndSort();
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    applyGroupFilterAndSort();
                }
            });
        }

        function filterGroups(filter) {
            currentFilter = filter;

            // Update slider position
            const activeBtn = document.getElementById(`filter-${filter}`);
            const slider = document.getElementById('filter-slider');
            const rect = activeBtn.getBoundingClientRect();
            const container = activeBtn.parentElement.getBoundingClientRect();
            slider.style.width = rect.width + 'px';
            slider.style.left = (rect.left - container.left) + 'px';

            applyGroupFilterAndSort();
        }

        function sortGroups(sortBy) {
            currentSort = sortBy;
            applyGroupFilterAndSort();
        }

        function toggleGroupSortOrder() {
            sortAscending = !sortAscending;
            const icon = document.getElementById('sort-order-icon');
            icon.className = sortAscending ? 'fas fa-arrow-up text-gray-700 text-base' :
                'fas fa-arrow-down text-gray-700 text-base';
            applyGroupFilterAndSort();
        }

        function applyGroupFilterAndSort() {
            const container = document.getElementById('groups-container');
            const groups = Array.from(container.querySelectorAll('[data-group-id]'));
            const searchTerm = document.getElementById('searchGroups')?.value.toLowerCase().trim() || '';
            let visibleCount = 0;

            // Filter
            groups.forEach(group => {
                const isPrivate = group.dataset.isPrivate === '1';
                const groupName = group.dataset.name.toLowerCase();
                const nameElement = group.querySelector('.group-name');
                const originalName = group.dataset.name;

                let show = false;

                // Apply privacy filter
                if (currentFilter === 'all') {
                    show = true;
                } else if (currentFilter === 'public' && !isPrivate) {
                    show = true;
                } else if (currentFilter === 'private' && isPrivate) {
                    show = true;
                }

                // Apply search filter and highlight
                if (show && searchTerm) {
                    if (!groupName.includes(searchTerm)) {
                        show = false;
                    } else {
                        // Highlight matching text
                        const regex = new RegExp(`(${searchTerm})`, 'gi');
                        const highlightedName = originalName.replace(regex,
                            '<span class="bg-yellow-200">$1</span>');
                        nameElement.innerHTML = highlightedName;
                    }
                } else {
                    // Reset to original name
                    nameElement.textContent = originalName;
                }

                if (show) visibleCount++;
                group.style.display = show ? 'block' : 'none';
            });

            // Handle "no results" message
            let noGroupsMessage = document.getElementById('no-groups-message');
            const dynamicNoResultsMessage = document.getElementById('no-groups-search-message');

            if (visibleCount === 0 && searchTerm) {
                // Hide default message
                if (noGroupsMessage) noGroupsMessage.style.display = 'none';

                // Show or create search-specific message
                if (dynamicNoResultsMessage) {
                    dynamicNoResultsMessage.querySelector('p').textContent = `No groups found matching "${searchTerm}"`;
                    dynamicNoResultsMessage.style.display = 'block';
                } else {
                    const noResultsDiv = document.createElement('div');
                    noResultsDiv.id = 'no-groups-search-message';
                    noResultsDiv.className = 'text-center py-12 text-gray-500';
                    noResultsDiv.innerHTML = `
                        <i class="fas fa-users text-4xl mb-4"></i>
                        <p class="text-lg">No groups found matching "${searchTerm}"</p>
                    `;
                    container.appendChild(noResultsDiv);
                }
            } else {
                // Hide search message
                if (dynamicNoResultsMessage) dynamicNoResultsMessage.style.display = 'none';

                // Show/hide default message
                if (noGroupsMessage) {
                    noGroupsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            // Sort
            const visibleGroups = groups.filter(group => group.style.display !== 'none');
            visibleGroups.sort((a, b) => {
                let aVal, bVal;

                switch (currentSort) {
                    case 'created_date':
                        aVal = new Date(a.dataset.createdAt);
                        bVal = new Date(b.dataset.createdAt);
                        break;
                    case 'members':
                        aVal = parseInt(a.dataset.membersCount);
                        bVal = parseInt(b.dataset.membersCount);
                        break;
                    case 'posts':
                        aVal = parseInt(a.dataset.postsCount);
                        bVal = parseInt(b.dataset.postsCount);
                        break;
                    case 'name':
                        aVal = a.dataset.name.toLowerCase();
                        bVal = b.dataset.name.toLowerCase();
                        break;
                }

                if (aVal < bVal) return sortAscending ? -1 : 1;
                if (aVal > bVal) return sortAscending ? 1 : -1;
                return 0;
            });

            // Reorder
            visibleGroups.forEach(group => container.appendChild(group));
        }

        async function deleteGroup(groupId, groupName) {
            const confirmed = await alertConfirm(
                `Are you sure you want to delete the group "${groupName}"? This action cannot be undone and will delete all posts in this group.`,
                'Confirm Delete'
            );

            if (!confirmed) {
                return;
            }

            fetch(`/admin/groups/${groupId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const groupCard = document.querySelector(`[data-group-id="${groupId}"]`);
                        if (groupCard) {
                            groupCard.remove();
                        }
                        alertInfo('Group deleted successfully', 'Success');
                    } else {
                        alertInfo(data.message || 'Failed to delete group', 'Error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertInfo('An error occurred while deleting the group', 'Error');
                });
        }
    </script>
@endsection
