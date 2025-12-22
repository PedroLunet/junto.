@if ($groupNotifications->count() > 0)
    <div class="space-y-3 sm:space-y-4">
        @foreach ($groupNotifications as $notification)
            @php
                $type = $notification->type ?? null;
                $typeColors = [
                    'group_invite' => [
                        'bg' => 'bg-cyan-50',
                        'text' => 'text-cyan-700',
                        'border' => 'border-cyan-200',
                        'icon' => 'fas fa-door-open',
                    ],
                    'group_join' => [
                        'bg' => 'bg-teal-50',
                        'text' => 'text-teal-700',
                        'border' => 'border-teal-200',
                        'icon' => 'fas fa-users',
                    ],
                ];
                $colors = $typeColors[$type] ?? null;
                $isInvite = $type === 'group_invite';
                $groupInvite = $notification->groupInviteRequest;
                $group = $groupInvite?->group;
                $request = \App\Models\Request::where('notificationid', $notification->id)->first();
                $isPending = $request && $request->status === 'pending';
            @endphp
            <div class="notification-item bg-white border-l-4 {{ $notification->isread ? 'border-gray-200' : 'border-[#820263]' }} rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 sm:p-6"
                id="notification-{{ $notification->id }}">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3 sm:gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3">
                            <div class="text-[#820263] text-xl mt-1 shrink-0">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if ($colors)
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }}">
                                            <i class="{{ $colors['icon'] }} mr-1.5"></i>
                                            {{ str_replace('_', ' ', ucfirst($type)) }}
                                        </span>
                                    </div>
                                @endif
                                <p
                                    class="text-gray-900 text-lg {{ !$notification->isread ? 'font-bold' : 'font-normal' }} leading-relaxed">
                                    {{ $notification->message }}
                                </p>
                                @if ($group)
                                    <div class="mt-2 flex items-center gap-2">
                                        <i class="fas fa-layer-group text-gray-500 text-sm"></i>
                                        <a href="{{ route('groups.show', $group->id) }}"
                                            class="text-[#820263] hover:text-[#600149] font-semibold text-sm">
                                            {{ $group->name }}
                                        </a>
                                    </div>
                                @endif
                                <div class="flex items-center gap-3 mt-3">
                                    <p class="text-sm text-gray-500">
                                        {{ $notification->createdat->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto sm:flex-shrink-0 flex-wrap justify-start sm:justify-end">
                        @if ($isInvite && $isPending && $group)
                            <form
                                action="{{ route('groups.acceptInvite', ['group' => $group->id, 'requestId' => $notification->id]) }}"
                                method="POST" class="flex-1 sm:flex-none">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center text-xs sm:text-sm text-white font-semibold px-3 sm:px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 transition-colors">
                                    <i class="fas fa-check mr-2"></i>
                                    <span>Accept</span>
                                </button>
                            </form>
                            <form
                                action="{{ route('groups.rejectInvite', ['group' => $group->id, 'requestId' => $notification->id]) }}"
                                method="POST" class="flex-1 sm:flex-none">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center text-xs sm:text-sm text-white font-semibold px-3 sm:px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 transition-colors">
                                    <i class="fas fa-times mr-2"></i>
                                    <span>Reject</span>
                                </button>
                            </form>
                        @endif
                        @if (!$notification->isread)
                            <button onclick="markAsRead({{ $notification->id }})"
                                class="inline-flex items-center text-xs sm:text-sm text-white font-semibold px-3 sm:px-4 py-2 rounded-lg bg-[#820263] hover:bg-[#600149] transition-colors flex-1 sm:flex-none justify-center">
                                <i class="fas fa-check mr-2"></i>
                                <span class="hidden sm:inline">Mark read</span>
                                <span class="sm:hidden">Read</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($groupNotifications->hasPages())
        <div class="mt-8 sm:mt-12 flex justify-center items-center gap-2 sm:gap-3 flex-wrap">
            @if ($groupNotifications->onFirstPage())
                <span class="px-2 sm:px-4 py-2 text-gray-400 text-xs sm:text-sm font-medium">← Previous</span>
            @else
                <a href="{{ $groupNotifications->previousPageUrl() }}"
                    class="px-2 sm:px-4 py-2 text-gray-700 hover:text-[#820263] font-medium text-xs sm:text-sm border border-gray-300 rounded-lg hover:border-[#820263] transition-colors">←
                    Previous</a>
            @endif

            @foreach ($groupNotifications->getUrlRange(1, $groupNotifications->lastPage()) as $page => $url)
                @if ($page == $groupNotifications->currentPage())
                    <span
                        class="px-2 sm:px-4 py-2 bg-[#820263] text-white rounded-lg font-semibold text-xs sm:text-sm">{{ $page }}</span>
                @else
                    <a href="{{ $url }}"
                        class="px-2 sm:px-4 py-2 text-gray-700 hover:text-[#820263] font-medium text-xs sm:text-sm border border-gray-300 rounded-lg hover:border-[#820263] transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            @if ($groupNotifications->hasMorePages())
                <a href="{{ $groupNotifications->nextPageUrl() }}"
                    class="px-2 sm:px-4 py-2 text-gray-700 hover:text-[#820263] font-medium text-xs sm:text-sm border border-gray-300 rounded-lg hover:border-[#820263] transition-colors">Next
                    →</a>
            @else
                <span class="px-2 sm:px-4 py-2 text-gray-400 text-xs sm:text-sm font-medium">Next →</span>
            @endif
        </div>
    @endif
@else
    <div class="text-center py-20">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 mb-6 text-gray-400">
            <i class="fas fa-users text-4xl"></i>
        </div>
        <p class="text-2xl font-bold text-gray-900">No group notifications yet</p>
        <p class="text-gray-500 mt-2 text-lg">You'll see group invites and join requests here</p>
    </div>
@endif
