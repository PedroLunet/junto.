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

            <!-- Reported Content Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                @if ($report->post_id)
                    <span class="font-medium text-gray-900">Reported Post</span>

                    <p class="text-xl text-gray-600">
                        Post ID: #{{ $report->post_id }}
                        @if ($report->post_author_username)
                            <br>Author:
                            {{ $report->post_author_name }}
                            (@<span>{{ $report->post_author_username }}</span>)
                        @endif
                    </p>
                @elseif($report->comment_id)
                    <span class="font-medium text-gray-900">Reported Comment</span>

                    <p class="text-xl text-gray-600">
                        Comment ID: #{{ $report->comment_id }}
                        @if ($report->comment_author_username)
                            <br>Author: <a href="/{{ $report->comment_author_username }}"
                                class="text-purple-600 hover:underline">
                                {{ $report->comment_author_name }}
                                (@<span>{{ $report->comment_author_username }}</span>)
                            </a>
                        @endif
                    </p>
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
