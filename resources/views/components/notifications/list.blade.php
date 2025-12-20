@if($notifications->count() > 0)
    <div class="space-y-4">
        @foreach($notifications as $notification)
            @php
                $isSnoozed = $notification->snoozed_until && $notification->snoozed_until > now();
                $typeColors = [
                    'comment' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'fas fa-comment'],
                    'like' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'icon' => 'fas fa-heart'],
                    'activity' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'icon' => 'fas fa-star'],
                    'friend_request' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'icon' => 'fas fa-user-plus'],
                    'group_invite' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'border' => 'border-cyan-200', 'icon' => 'fas fa-door-open'],
                    'group_join' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'border' => 'border-teal-200', 'icon' => 'fas fa-users'],
                ];
                $type = $notification->type ?? null;
                $colors = $typeColors[$type] ?? null;
            @endphp
            <div class="notification-item bg-white border-l-4 {{ $notification->isread ? 'border-gray-200' : 'border-[#820263]' }} rounded-xl shadow-sm hover:shadow-md transition-shadow p-6"
                id="notification-{{ $notification->id }}">
                <div class="flex justify-between items-start gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3">
                            <div class="text-[#820263] text-xl mt-1 shrink-0">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($colors)
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }}">
                                            <i class="{{ $colors['icon'] }} mr-1.5"></i>
                                            {{ str_replace('_', ' ', ucfirst($type)) }}
                                        </span>
                                    </div>
                                @endif
                                <p class="text-gray-900 text-lg {{ !$notification->isread ? 'font-bold' : 'font-normal' }} leading-relaxed">
                                    {{ $notification->message }}
                                </p>
                                <div class="flex items-center gap-3 mt-3">
                                    <p class="text-sm text-gray-500">
                                        {{ $notification->createdat->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0 flex-wrap justify-end">
                        @if(!$notification->isread)
                            <button onclick="markAsRead({{ $notification->id }})" 
                                class="inline-flex items-center text-sm text-white font-semibold px-4 py-2 rounded-lg bg-[#820263] hover:bg-[#600149] transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Mark read
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($notifications->hasPages())
        <div class="mt-12 flex justify-center items-center gap-3 flex-wrap">
            @if ($notifications->onFirstPage())
                <span class="px-4 py-2 text-gray-400 text-sm font-medium">← Previous</span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}" class="px-4 py-2 text-gray-700 hover:text-[#820263] font-medium text-sm border border-gray-300 rounded-lg hover:border-[#820263] transition-colors">← Previous</a>
            @endif

            @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                @if ($page == $notifications->currentPage())
                    <span class="px-4 py-2 bg-[#820263] text-white rounded-lg font-semibold text-sm">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-4 py-2 text-gray-700 hover:text-[#820263] font-medium text-sm border border-gray-300 rounded-lg hover:border-[#820263] transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            @if ($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}" class="px-4 py-2 text-gray-700 hover:text-[#820263] font-medium text-sm border border-gray-300 rounded-lg hover:border-[#820263] transition-colors">Next →</a>
            @else
                <span class="px-4 py-2 text-gray-400 text-sm font-medium">Next →</span>
            @endif
        </div>
    @endif
@else
    <div class="text-center py-20">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 mb-6 text-gray-400">
            <i class="fas fa-bell text-4xl"></i>
        </div>
        <p class="text-2xl font-bold text-gray-900">No notifications yet</p>
        <p class="text-gray-500 mt-2 text-lg">Check back later for updates on your activity</p>
    </div>
@endif
