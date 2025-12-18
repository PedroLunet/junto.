@extends('layouts.admin')

@section('page-title', 'Groups')

@section('content')
    <div class="space-y-6">
        <!-- Header with Filters and Sort -->
        <div class="flex items-center gap-4">
            <!-- Sort By Dropdown -->
            <x-ui.sort-dropdown :options="[
                'created_date' => 'Created Date',
                'members' => 'Members',
                'posts' => 'Posts',
                'name' => 'Name',
            ]" defaultValue="created_date" onSort="sortGroups"
                onToggleOrder="toggleGroupSortOrder" />

            <!-- Filter Tabs -->
            <x-ui.filter-tabs :filters="[
                'all' => ['label' => 'All', 'onclick' => 'filterGroups(\'all\')'],
                'public' => ['label' => 'Public', 'onclick' => 'filterGroups(\'public\')'],
                'private' => ['label' => 'Private', 'onclick' => 'filterGroups(\'private\')'],
            ]" activeFilter="all" />
        </div>

        <!-- Groups List -->
        <div id="groups-container" class="space-y-6">
            @foreach ($groups as $group)
                <x-admin.group-card :group="$group" />
            @endforeach

            @if (count($groups) === 0)
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p class="text-lg">No groups found.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        let currentSort = 'created_date';
        let sortAscending = false;

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

            // Filter
            groups.forEach(group => {
                const isPrivate = group.dataset.isPrivate === '1';
                let show = false;

                if (currentFilter === 'all') {
                    show = true;
                } else if (currentFilter === 'public' && !isPrivate) {
                    show = true;
                } else if (currentFilter === 'private' && isPrivate) {
                    show = true;
                }

                group.style.display = show ? 'block' : 'none';
            });

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

        function deleteGroup(groupId, groupName) {
            if (!confirm(
                    `Are you sure you want to delete the group "${groupName}"? This action cannot be undone and will delete all posts and members.`
                )) {
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
                        alert('Group deleted successfully');
                    } else {
                        alert(data.message || 'Failed to delete group');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the group');
                });
        }
    </script>
@endsection
