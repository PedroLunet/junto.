<div class="bg-white rounded-2xl shadow border border-gray-200" data-report-id="{{ $report->id }}">
    <!-- Report Header -->
    <div class="flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 transition-colors rounded-2xl"
        onclick="toggleReport({{ $report->id }})">
        <div class="flex-1">
            <span class="text-base text-gray-500 mb-2 block">
                {{ \Carbon\Carbon::parse($report->createdat)->diffForHumans() }}
            </span>

            <div class="flex gap-2 items-center">
                <h3 class="text-xl font-semibold text-gray-900 whitespace-nowrap">Reason:</h3>
                <p class="text-xl text-gray-700">{{ $report->reason }}</p>
            </div>
        </div>

        <div class="flex items-center">
            <x-ui.badge :variant="$report->status === 'accepted' ? 'online' : ($report->status === 'rejected' ? 'offline' : 'pending')" size="md">
                {{ ucfirst($report->status) }}
            </x-ui.badge>
        </div>

        <!-- Chevron Icon -->
        <i class="fas fa-chevron-down text-gray-400 text-xl transition-transform duration-300 ml-4 self-center"
            id="chevron-{{ $report->id }}"></i>

    </div>

    <!-- Collapsible Content -->
    <div class="hidden px-6 pb-6 overflow-hidden transition-all duration-300 ease-in-out max-h-0 opacity-0"
        id="report-content-{{ $report->id }}"
        style="transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out, padding 0.3s ease-in-out;">
        <!-- Reported Content -->
        <div class="mb-4 pt-4 border-t border-gray-200">
            @if ($report->post_id && isset($report->post))
                @if ($report->post->is_review ?? false)
                    <x-posts.post-review :post="$report->post" :showAuthor="true" :isViewOnly="true" />
                @else
                    <x-posts.post-standard :post="$report->post" :showAuthor="true" :isViewOnly="true" />
                @endif
            @elseif($report->comment_id && isset($report->comment))
                <div class="bg-gray-50 rounded-lg p-4">
                    <x-posts.comment.comment :comment="$report->comment" :isViewOnly="true" />
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        @if ($report->status === 'pending')
            <div class="flex gap-3 justify-end">
                <x-ui.button variant="success" onclick="event.stopPropagation(); acceptReport({{ $report->id }})">
                    Accept & Delete Content
                </x-ui.button>
                <x-ui.button variant="danger" onclick="event.stopPropagation(); rejectReport({{ $report->id }})">
                    Reject Report
                </x-ui.button>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleReport(reportId) {
        const content = document.getElementById(`report-content-${reportId}`);
        const chevron = document.getElementById(`chevron-${reportId}`);

        if (content.classList.contains('hidden')) {
            // Show content with animation
            content.classList.remove('hidden');

            // Trigger reflow to enable animation
            content.offsetHeight;

            content.style.maxHeight = content.scrollHeight + 'px';
            content.classList.remove('opacity-0');
            content.classList.add('opacity-100');
            chevron.classList.add('rotate-180');
        } else {
            // Hide content with animation
            content.style.maxHeight = '0';
            content.classList.remove('opacity-100');
            content.classList.add('opacity-0');
            chevron.classList.remove('rotate-180');

            // Wait for animation to complete before hiding
            setTimeout(() => {
                content.classList.add('hidden');
            }, 300);
        }
    }
</script>
