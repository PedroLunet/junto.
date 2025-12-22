<div class="space-y-6 reports-list-container">
    @forelse($reports as $report)
        <div class="report-item" data-status="{{ $report->status }}" data-report-id="{{ $report->id }}">
            <x-admin.reports.report-card :report="$report" />
        </div>
    @empty
        <x-ui.empty-state icon="fa-inbox" title="No Reports Found" description="There are no reports to review."
            height="min-h-[calc(100vh-16rem)]" class="reports-empty-state" />
    @endforelse
</div>
<script>
    // Ensure empty state appears dynamically when no reports match the filter
    window.applyReportFilterAndSort = function() {
        let filteredReports = typeof allReports !== 'undefined' && currentFilter !== 'all' ?
            allReports.filter(r => r.status === currentFilter) :
            (typeof allReports !== 'undefined' ? allReports : []);

        // Sort logic (optional, can be improved to match your main JS)
        if (typeof currentSort !== 'undefined' && currentSort === 'created_date') {
            filteredReports.sort((a, b) => {
                const diff = new Date(b.createdat) - new Date(a.createdat);
                return sortAscending ? -diff : diff;
            });
        }

        const container = document.querySelector('.reports-list-container');
        const reportItems = container ? container.querySelectorAll('.report-item') : [];
        let emptyState = container ? container.querySelector('.reports-empty-state') : null;

        // Create a map of report IDs to their elements
        const reportMap = new Map();
        reportItems.forEach(item => {
            const reportId = item.dataset.reportId;
            if (reportId) reportMap.set(parseInt(reportId), item);
        });

        // Reorder and show/hide based on filtered array
        filteredReports.forEach(report => {
            const element = reportMap.get(report.id);
            if (element) {
                container.appendChild(element);
                element.style.display = 'block';
            }
        });
        reportItems.forEach(item => {
            const reportId = item.dataset.reportId;
            if (!filteredReports.find(r => r.id === parseInt(reportId))) {
                item.style.display = 'none';
            }
        });

        // Show/hide or dynamically add/remove empty state
        if (filteredReports.length === 0) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'reports-empty-state';
                emptyState.innerHTML = `
                <div class="flex flex-col items-center justify-center text-gray-500 min-h-[calc(100vh-16rem)]">
                    <div class="text-center">
                        <div class="bg-gray-200 rounded-full p-6 inline-block mb-4">
                            <i class="fas fa-inbox text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-700">No Reports Found</h3>
                        <p class="mt-2">There are no reports that match this filter.</p>
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
</script>
