@extends('layouts.admin')

@section('page-title', 'Unblock Appeals')

@section('content')
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div class="mx-20 mt-10 mb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Unblock Appeals</h1>
                    <p class="text-gray-600 mt-2 text-base">Review and manage user appeal requests</p>
                </div>
                <div class="flex items-center gap-6">
                    <!-- Sort By Dropdown -->
                    <x-ui.sort-dropdown :options="[
                        'created_date' => 'Created Date',
                        'user_name' => 'User Name',
                    ]" defaultValue="created_date" />

                    <!-- Filter Tabs -->
                    <x-ui.filter-tabs :filters="[
                        'all' => [
                            'label' => 'All',
                            'count' => $counts['all'],
                            'onclick' => 'filterAppeals(\'all\')',
                        ],
                        'pending' => [
                            'label' => 'Pending',
                            'count' => $counts['pending'],
                            'onclick' => 'filterAppeals(\'pending\')',
                        ],
                        'approved' => [
                            'label' => 'Approved',
                            'count' => $counts['approved'],
                            'onclick' => 'filterAppeals(\'approved\')',
                        ],
                        'rejected' => [
                            'label' => 'Rejected',
                            'count' => $counts['rejected'],
                            'onclick' => 'filterAppeals(\'rejected\')',
                        ],
                    ]" activeFilter="all" />
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div id="appeals-container" class="mx-20 my-6">
                @if ($appeals->isEmpty())
                    <x-ui.empty-state icon="fa-gavel" title="No Appeals to Review"
                        description="All unblock appeals have been processed" height="min-h-[calc(100vh-16rem)]" />
                @else
                    <div class="grid gap-6">
                        @foreach ($appeals as $appeal)
                            <div class="appeal-item">
                                <x-admin.appeal-card :appeal="$appeal" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-ui.confirm />
    <x-admin.admin-notes-modal />

@endsection

@push('scripts')
    <script>
        let currentFilter = 'all';
        let currentSort = 'created_date';
        let sortAscending = false; // false = descending (default)
        let allAppeals = @json($appeals);

        function toggleSortOrder() {
            sortAscending = !sortAscending;
            const icon = document.getElementById('sort-order-icon');

            if (sortAscending) {
                icon.classList.remove('fa-arrow-down');
                icon.classList.add('fa-arrow-up');
            } else {
                icon.classList.remove('fa-arrow-up');
                icon.classList.add('fa-arrow-down');
            }

            applyFilterAndSort();
        }

        function sortAppeals(sortBy) {
            currentSort = sortBy;
            applyFilterAndSort();
        }

        function applyFilterAndSort() {
            let filteredAppeals = currentFilter === 'all' ?
                allAppeals :
                allAppeals.filter(a => a.status === currentFilter);

            // Sort appeals
            if (currentSort === 'created_date') {
                filteredAppeals.sort((a, b) => {
                    const diff = new Date(b.createdat) - new Date(a.createdat);
                    return sortAscending ? -diff : diff;
                });
            } else if (currentSort === 'user_name') {
                filteredAppeals.sort((a, b) => {
                    const aName = (a.user?.name || '').toLowerCase();
                    const bName = (b.user?.name || '').toLowerCase();
                    const diff = aName.localeCompare(bName);
                    return sortAscending ? diff : -diff;
                });
            }

            // Update display
            const appealItems = document.querySelectorAll('.appeal-item');
            const container = document.querySelector('#appeals-container .grid');

            // Create a map of appeal IDs to their elements
            const appealMap = new Map();
            appealItems.forEach(item => {
                const appealId = item.querySelector('[id^="appeal-"]')?.id.replace('appeal-', '');
                if (appealId) appealMap.set(parseInt(appealId), item);
            });

            // Reorder based on sorted array
            filteredAppeals.forEach(appeal => {
                const element = appealMap.get(appeal.id);
                if (element) {
                    container.appendChild(element);
                    element.style.display = 'block';
                }
            });

            // Hide appeals not in filtered list
            appealItems.forEach(item => {
                const appealId = item.querySelector('[id^="appeal-"]')?.id.replace('appeal-', '');
                if (!filteredAppeals.find(a => a.id === parseInt(appealId))) {
                    item.style.display = 'none';
                }
            });
        }

        function filterAppeals(status) {
            currentFilter = status;

            // Update slider position
            const activeBtn = document.getElementById(`filter-${status}`);
            const slider = document.getElementById('filter-slider');
            const rect = activeBtn.getBoundingClientRect();
            const container = activeBtn.parentElement.getBoundingClientRect();
            slider.style.width = rect.width + 'px';
            slider.style.left = (rect.left - container.left) + 'px';

            // Update button styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-white', 'shadow-sm');
                btn.classList.add('text-gray-700');
            });

            activeBtn.classList.add('bg-white', 'shadow-sm');
            activeBtn.classList.remove('text-gray-700');

            applyFilterAndSort();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            filterAppeals('all');
        });

        async function approveAppeal(appealId) {
            const confirmed = await alertConfirm(
                'Are you sure you want to approve this appeal and unblock this user?',
                'Confirm Approval'
            );

            if (!confirmed) {
                return;
            }

            fetch(`/admin/appeals/${appealId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alertInfo(data.message || 'Failed to approve appeal', 'Error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertInfo('An error occurred while approving the appeal', 'Error');
                });
        }

        async function rejectAppeal(appealId) {
            const confirmed = await alertConfirm(
                'Are you sure you want to reject this appeal? The user will remain blocked.',
                'Confirm Rejection'
            );

            if (!confirmed) {
                return;
            }

            // Open the admin notes modal
            openAdminNotesModal(appealId);
        }
    </script>
@endpush
