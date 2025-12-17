<div class="space-y-4">
    @forelse($reports as $report)
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <!-- Report Header -->
            <div class="flex items-center justify-between mb-3">
                <span class="text-xl text-gray-500">
                    {{ \Carbon\Carbon::parse($report->createdat)->diffForHumans() }}
                </span>
                <x-ui.badge :variant="$report->status === 'accepted' ? 'online' : ($report->status === 'rejected' ? 'offline' : 'pending')" size="lg">
                    {{ ucfirst($report->status) }}
                </x-ui.badge>
            </div>

            <!-- Report Reason -->
            <div class="mb-4 flex gap-4">
                <h3 class="text-3xl font-semibold text-gray-900 whitespace-nowrap">Reason:</h3>
                <p class="text-3xl text-gray-700">{{ $report->reason }}</p>
            </div>

            <!-- Reported Content -->
            <div class="mb-4">
                @if ($report->post_id && isset($report->post))
                    @if ($report->post->is_review ?? false)
                        <x-posts.post-review :post="$report->post" :showAuthor="true" :isViewOnly="true" />
                    @else
                        <x-posts.post-standard :post="$report->post" :showAuthor="true" :isViewOnly="true" />
                    @endif
                @elseif($report->comment_id && isset($report->comment))
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <i class="fas fa-comment text-purple-600"></i>
                            <span class="font-medium text-gray-900">Reported Comment</span>
                        </div>
                        <x-posts.comment.comment :comment="$report->comment" :isViewOnly="true" />
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            @if ($report->status === 'pending')
                <div class="flex gap-3 justify-end">
                    <x-ui.button variant="success" onclick="acceptReport({{ $report->id }})">
                        Accept & Delete Content
                    </x-ui.button>
                    <x-ui.button variant="danger" onclick="rejectReport({{ $report->id }})">
                        Reject Report
                    </x-ui.button>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-500 text-2xl">No reports found</p>
        </div>
    @endforelse
</div>
