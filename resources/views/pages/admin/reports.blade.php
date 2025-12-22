@extends('layouts.admin')

@section('content')
    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div class="mx-4 sm:mx-8 lg:mx-20 mt-6 sm:mt-8 lg:mt-10 mb-4 flex flex-col gap-4">
                <div>
                    <!-- Title -->
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Reported Content</h1>
                    <!-- Description -->
                    <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base lg:mt-0">Review and manage reported posts and
                        comments</p>
                </div>

                <!-- Controls: Sort and Filter Tabs -->
                <div class="flex flex-col lg:flex-row w-full lg:w-auto gap-4 items-center justify-center">
                    <div class="flex justify-start w-full">
                        <x-ui.sort-dropdown :options="[
                            'created_date' => 'Created Date',
                            'popularity' => 'Popularity',
                        ]" defaultValue="created_date" />
                    </div>
                    <div class="w-full flex justify-center lg:justify-end mt-2 lg:mt-0">
                        <x-ui.filter-tabs :filters="[
                            'all' => [
                                'label' => 'All',
                                'count' => $counts['all'],
                                'onclick' => 'filterReports(\'all\')',
                            ],
                            'pending' => [
                                'label' => 'Pending',
                                'count' => $counts['pending'],
                                'onclick' => 'filterReports(\'pending\')',
                            ],
                            'rejected' => [
                                'label' => 'Rejected',
                                'count' => $counts['rejected'],
                                'onclick' => 'filterReports(\'rejected\')',
                            ],
                        ]" activeFilter="all" :hideCountsOnMobile="true" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div id="reports-container" class="mx-4 sm:mx-8 lg:mx-20 my-4 sm:my-6">
                <x-admin.reports.reports-list :reports="collect($reports)" />
            </div>
        </div>
    </div>

    <x-ui.confirm />
@endsection

@push('scripts')
    <script>
        // === ALERT CARD: define first so it's always available ===
        window.showAlertCard = function(type, title, message) {
            // Remove any existing alert card
            const existing = document.getElementById('js-dynamic-alert-card');
            if (existing) existing.remove();
            // Create wrapper div
            const wrapper = document.createElement('div');
            wrapper.id = 'js-dynamic-alert-card';
            wrapper.innerHTML = `
            <div class='fixed top-6 right-6 z-50 flex items-start gap-3 px-4 py-4 rounded-2xl border ${type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'} shadow-sm mb-4 min-w-[280px] max-w-xs transition-all duration-300 ease-in-out opacity-0 translate-x-full'>
                <div class='shrink-0 flex items-center justify-center w-8 h-8 rounded-full ${type === 'success' ? 'bg-green-200' : 'bg-red-200'}'>
                    ${type === 'success'
                        ? `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke-width='2' class='stroke-green-400 fill-green-50'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4' class='stroke-green-600'/></svg>`
                        : `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke-width='2' class='stroke-red-400 fill-red-50'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 9l-6 6m0-6l6 6' class='stroke-red-600'/></svg>`}
                </div>
                <div class='flex-1'>
                    <div class='font-semibold text-base ${type === 'success' ? 'text-green-600' : 'text-red-600'} mb-0.5'>${title}</div>
                    <div class='text-gray-700 text-sm'>${message}</div>
                </div>
                <button type='button' class='absolute top-2 right-2' onclick='this.closest("#js-dynamic-alert-card").remove()'>
                    <i class='fa fa-times w-5 h-5'></i>
                </button>
            </div>
        `;
            document.body.appendChild(wrapper);
            // Animate in
            setTimeout(() => {
                const alert = wrapper.firstElementChild;
                alert.classList.remove('opacity-0', 'translate-x-full');
                alert.classList.add('opacity-100', 'translate-x-0');
            }, 10);
            // Animate out after 5s
            setTimeout(() => {
                const alert = wrapper.firstElementChild;
                if (!alert) return;
                alert.classList.remove('opacity-100', 'translate-x-0');
                alert.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => {
                    wrapper.remove();
                }, 300);
            }, 5000);
        }

        // === PAGE LOGIC ===
        let currentFilter = 'all';
        let currentSort = 'created_date';
        let sortAscending = false; // false = descending (default)
        let allReports = @json($reports);

        function toggleReport(reportId) {
            const content = document.getElementById(`content-${reportId}`);
            const chevron = document.getElementById(`chevron-${reportId}`);

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }

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

        function sortReports(sortBy) {
            currentSort = sortBy;
            applyFilterAndSort();
        }

        function applyFilterAndSort() {
            if (typeof window.applyReportFilterAndSort === 'function') {
                window.applyReportFilterAndSort();
            }
        }

        function filterReports(status) {
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
            filterReports('all');
        });

        async function acceptReport(reportId) {
            const confirmed = await alertConfirm(
                'Are you sure you want to accept this report? This will permanently delete the reported content.',
                'Confirm Delete'
            );

            if (!confirmed) {
                return;
            }

            fetch(`/admin/reports/${reportId}/accept`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlertCard('success', 'Success', 'Report accepted successfully.');
                        window.location.reload();
                    } else {
                        showAlertCard('error', 'Error', data.message || 'Failed to accept report');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlertCard('error', 'Error', 'An error occurred while processing the report');
                });
        }

        async function rejectReport(reportId) {
            const confirmed = await alertConfirm(
                'Are you sure you want to reject this report? The reported content will remain on the platform.',
                'Confirm Rejection'
            );

            if (!confirmed) {
                return;
            }

            fetch(`/admin/reports/${reportId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlertCard('success', 'Success', 'Report rejected successfully.');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showAlertCard('error', 'Error', data.message || 'Failed to reject report');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlertCard('error', 'Error', 'An error occurred while processing the report');
                });
        }
    </script>
@endpush
