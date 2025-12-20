<div class="space-y-6">
    @forelse($reports as $report)
        <div class="report-item" data-status="{{ $report->status }}">
            <x-admin.reports.report-card :report="$report" />
        </div>
    @empty
        <x-ui.empty-state icon="fa-inbox" title="No Reports Found" description="There are no reports to review."
            height="min-h-[calc(100vh-16rem)]" />
    @endforelse
</div>
