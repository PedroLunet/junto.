<div class="space-y-4">
    @forelse($reports as $report)
        <x-admin.report-card :report="$report" />
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-500 text-2xl">No reports found</p>
        </div>
    @endforelse
</div>
