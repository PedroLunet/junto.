@extends('layouts.admin')

@section('page-title', 'Reports')

@section('content')
    <div class="mx-20 my-10 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Reported Content</h1>
            <p class="text-gray-600 mt-2 text-2xl">Review and manage reported posts and comments</p>
        </div>
        <x-ui.filter-tabs :filters="[
            'all' => [
                'label' => 'All',
                'count' => count($reports),
                'onclick' => 'filterReports(\'all\')'
            ],
            'pending' => [
                'label' => 'Pending',
                'count' => collect($reports)->filter(fn($r) => $r->status === 'pending')->count(),
                'onclick' => 'filterReports(\'pending\')'
            ],
            'accepted' => [
                'label' => 'Accepted',
                'count' => collect($reports)->filter(fn($r) => $r->status === 'accepted')->count(),
                'onclick' => 'filterReports(\'accepted\')'
            ],
            'rejected' => [
                'label' => 'Rejected',
                'count' => collect($reports)->filter(fn($r) => $r->status === 'rejected')->count(),
                'onclick' => 'filterReports(\'rejected\')'
            ]
        ]" activeFilter="all" />
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
