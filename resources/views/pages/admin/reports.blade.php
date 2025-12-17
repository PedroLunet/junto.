@extends('layouts.admin')

@section('page-title', 'Reports')

@section('content')
    <div class="mx-20 my-10 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Reported Content</h1>
            <p class="text-gray-600 mt-2 text-2xl">Review and manage reported posts and comments</p>
        </div>
        <div class="inline-flex bg-gray-200 rounded-lg p-1 gap-0">
            <button onclick="filterReports('all')" id="filter-all"
                class="px-5 py-1.5 rounded-md text-sm font-medium transition-all filter-btn bg-white shadow-sm">
                All
                <span class="ml-2 text-gray-500">{{ count($reports) }}</span>
            </button>
            <div class="w-px bg-gray-300 my-1"></div>
            <button onclick="filterReports('pending')" id="filter-pending"
                class="px-5 py-1.5 rounded-md text-sm font-medium transition-all filter-btn">
                Pending
                <span
                    class="ml-2 text-gray-500">{{ collect($reports)->filter(fn($r) => $r->status === 'pending')->count() }}</span>
            </button>
            <div class="w-px bg-gray-300 my-1"></div>
            <button onclick="filterReports('accepted')" id="filter-accepted"
                class="px-5 py-1.5 rounded-md text-sm font-medium transition-all filter-btn">
                Accepted
                <span
                    class="ml-2 text-gray-500">{{ collect($reports)->filter(fn($r) => $r->status === 'accepted')->count() }}</span>
            </button>
            <div class="w-px bg-gray-300 my-1"></div>
            <button onclick="filterReports('rejected')" id="filter-rejected"
                class="px-5 py-1.5 rounded-md text-sm font-medium transition-all filter-btn">
                Rejected
                <span
                    class="ml-2 text-gray-500">{{ collect($reports)->filter(fn($r) => $r->status === 'rejected')->count() }}</span>
            </button>
        </div>
    </div>

    <div id="reports-container">
        <x-admin.reports-list :reports="collect($reports)" />
    </div>

    <x-ui.confirm />
@endsection

@push('scripts')
    <script>
        let currentFilter = 'all';

        function filterReports(status) {
            currentFilter = status;

            // Update button styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-white', 'shadow-sm');
                btn.classList.add('text-gray-700');
            });

            const activeBtn = document.getElementById(`filter-${status}`);
            activeBtn.classList.add('bg-white', 'shadow-sm');
            activeBtn.classList.remove('text-gray-700');

            // Filter reports
            document.querySelectorAll('.report-item').forEach(item => {
                if (status === 'all' || item.dataset.status === status) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
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
                        window.location.reload();
                    } else {
                        alertInfo(data.message || 'Failed to accept report', 'Error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertInfo('An error occurred while processing the report', 'Error');
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
                        window.location.reload();
                    } else {
                        alertInfo(data.message || 'Failed to reject report', 'Error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertInfo('An error occurred while processing the report', 'Error');
                });
        }
    </script>
@endpush
