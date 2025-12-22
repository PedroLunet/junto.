@extends('layouts.admin')

@section('content')
    @if (session('alert'))
        <x-ui.alert-card :type="session('alert.type', 'success')" :title="session('alert.title', '')"
            :message="session('alert.message', '')" :dismissible="true" />
    @endif
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div class="mx-4 sm:mx-8 lg:mx-20 mt-6 sm:mt-8 lg:mt-10 mb-4 flex flex-col gap-4">
                <div><!-- Title -->
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Unblock Appeals</h1>
                    <!-- Description -->
                    <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base lg:mt-0">Review and manage user appeal
                        requests
                    </p>
                </div>

                <!-- Controls: Sort and Filter Tabs -->
                <div class="flex flex-col lg:flex-row w-full lg:w-auto gap-4 items-center justify-center">
                    <div class="flex justify-start w-full">
                        <x-ui.sort-dropdown :options="[
            'created_date' => 'Created Date',
            'user_name' => 'User Name',
        ]" defaultValue="created_date" />
                    </div>
                    <div class="w-full flex justify-center lg:justify-end mt-2 lg:mt-0">
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
        ]" activeFilter="all" :hideCountsOnMobile="true" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div id="appeals-container" class="mx-2 sm:mx-8 lg:mx-20 my-6">
                <x-admin.appeals.appeals-list :appeals="$appeals" />
            </div>
        </div>
    </div>

    <x-ui.confirm />
    <x-admin.appeals.admin-notes-modal />

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

        window.applyFilterAndSort = function () {
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
            const container = document.querySelector('#appeals-container .grid');
            const appealItems = container ? container.querySelectorAll('.appeal-item') : [];
            let emptyState = container ? container.querySelector('.appeals-empty-state') : null;

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

            // Show/hide or dynamically add/remove empty state
            if (filteredAppeals.length === 0) {
                if (!emptyState) {
                    emptyState = document.createElement('div');
                    emptyState.className = 'appeals-empty-state';
                    emptyState.innerHTML = `
                            <div class="flex flex-col items-center justify-center text-gray-500 min-h-[calc(100vh-16rem)]">
                                <div class="text-center">
                                    <div class="bg-gray-200 rounded-full p-6 inline-block mb-4">
                                        <i class="fas fa-gavel text-4xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-xl font-medium text-gray-700">No Appeals Found</h3>
                                    <p class="mt-2">There are no appeals that match this filter.</p>
                                </div>
                            </div>
                        `;
                    container.appendChild(emptyState);
                }
                emptyState.style.display = '';
            } else if (emptyState) {
                emptyState.style.display = 'none';
            }
        }

        window.filterAppeals = function (status) {
            currentFilter = status;
            window.applyFilterAndSort();
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
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(async response => {
                    if (response.status === 419 || response.status === 401) {
                        showAlertCard('error', 'Session Expired', 'Your session has expired. Reloading the page...');
                        setTimeout(() => window.location.reload(), 2000);
                        return null;
                    }
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) return; // Handled above (e.g. reload)

                    if (data.success) {
                        // Remove the approved appeal from the UI
                        const appealCard = document.getElementById(`appeal-${appealId}`)?.closest('.appeal-item');
                        if (appealCard) appealCard.remove();
                        // Optionally update counts, empty state, etc. (not shown here)
                        showAlertCard('success', 'Appeal Approved', data.message ||
                            'Appeal approved and user unblocked successfully');
                    } else {
                        showAlertCard('error', 'Error', data.message || 'Failed to approve appeal');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlertCard('error', 'Error', 'An error occurred while approving the appeal');
                });
        }

        // Dynamically injects an alert card at the top right
        function showAlertCard(type, title, message) {
            // Remove any existing alert cards
            document.querySelectorAll('.dynamic-alert-card').forEach(e => e.remove());
            const alertId = 'alert-card-' + Math.random().toString(36).substr(2, 9);
            const isSuccess = type === 'success';
            const bg = isSuccess ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
            const iconBg = isSuccess ? 'bg-green-200' : 'bg-red-200';
            const iconColor = isSuccess ? 'text-green-600' : 'text-red-600';
            const icon = isSuccess ?
                `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" class="stroke-green-400 fill-green-50"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" class="stroke-green-600"/></svg>` :
                `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" class="stroke-red-400 fill-red-50"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-6 6m0-6l6 6" class="stroke-red-600"/></svg>`;
            const alert = document.createElement('div');
            alert.id = alertId;
            alert.className =
                `dynamic-alert-card fixed top-6 right-6 z-50 flex items-start gap-3 px-4 py-4 rounded-2xl border ${bg} shadow-sm mb-4 min-w-[280px] max-w-xs transition-all duration-300 ease-in-out opacity-0 translate-x-full`;
            alert.innerHTML = `
                    <div class="shrink-0 flex items-center justify-center w-8 h-8 rounded-full ${iconBg}">
                        ${icon}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-base ${iconColor} mb-0.5">${title}</div>
                        <div class="text-gray-700 text-sm">${message}</div>
                    </div>
                    <button type="button" class="absolute top-2 right-2" onclick="this.closest('div').style.display='none'">
                        <i class="fa fa-times w-5 h-5"></i>
                    </button>
                `;
            document.body.appendChild(alert);
            // Animate in
            setTimeout(() => {
                alert.classList.remove('opacity-0', 'translate-x-full');
                alert.classList.add('opacity-100', 'translate-x-0');
            }, 10);
            // Animate out after 5s
            setTimeout(() => {
                alert.classList.remove('opacity-100', 'translate-x-0');
                alert.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
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