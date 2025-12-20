<x-ui.collapsible-card :id="$report->id" toggleFunction="toggleReport" dataAttribute="report-id">
    <x-slot name="header">
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
            <x-ui.badge :variant="$report->status === 'accepted' ? 'online' : ($report->status === 'rejected' ? 'offline' : 'pending')" size="xs" :icon="$report->status === 'accepted' ? 'fas fa-check' : ($report->status === 'rejected' ? 'fas fa-times' : 'fas fa-clock')">
                {{ ucfirst($report->status) }}
            </x-ui.badge>
        </div>
    </x-slot>

    <!-- reported Content -->
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

    <!-- action Buttons -->
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
</x-ui.collapsible-card>
