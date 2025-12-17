@extends('layouts.admin')

@section('page-title', 'Reports')

@section('content')
    <div class="mx-20 my-10">
        <h1 class="text-4xl font-bold text-gray-900">Reported Content</h1>
        <p class="text-gray-600 mt-2 text-2xl">Review and manage reported posts and comments</p>
    </div>

    <x-ui.tabs :tabs="[
        'all' => [
            'title' => 'All Reports',
            'content' => view('components.admin.reports-list', [
                'reports' => collect($reports),
            ])->render(),
        ],
        'pending' => [
            'title' => 'Pending',
            'content' => view('components.admin.reports-list', [
                'reports' => collect($reports)->filter(fn($r) => $r->status === 'pending'),
            ])->render(),
        ],
        'accepted' => [
            'title' => 'Accepted',
            'content' => view('components.admin.reports-list', [
                'reports' => collect($reports)->filter(fn($r) => $r->status === 'accepted'),
            ])->render(),
        ],
        'rejected' => [
            'title' => 'Rejected',
            'content' => view('components.admin.reports-list', [
                'reports' => collect($reports)->filter(fn($r) => $r->status === 'rejected'),
            ])->render(),
        ],
    ]" />
@endsection

@push('scripts')
    <script>
        function acceptReport(reportId) {
            if (!confirm(
                    'Are you sure you want to accept this report? This will permanently delete the reported content.')) {
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
                        alert(data.message || 'Failed to accept report');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the report');
                });
        }

        function rejectReport(reportId) {
            if (!confirm(
                    'Are you sure you want to reject this report? The reported content will remain on the platform.')) {
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
                        alert(data.message || 'Failed to reject report');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the report');
                });
        }
    </script>
@endpush
