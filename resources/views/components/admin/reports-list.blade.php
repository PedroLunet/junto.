<div class="space-y-6">
    @forelse($reports as $report)
        <div class="report-item" data-status="{{ $report->status }}">
            <x-admin.report-card :report="$report" />
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-500 text-2xl">No reports found</p>
        </div>
    @endforelse
</div>
