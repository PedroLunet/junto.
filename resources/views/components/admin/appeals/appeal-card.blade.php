@props(['appeal'])

<div id="appeal-{{ $appeal->id }}"
    class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition">
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-4 gap-4 sm:gap-0">
            <div class="flex items-center space-x-4">
                <div class="shrink-0">
                    @if ($appeal->user->profilepicture)
                        <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-200"
                            src="{{ $appeal->user->profilepicture }}" alt="{{ $appeal->user->name }}"
                            onerror="this.onerror=null; this.src='/profile/default.png';">
                    @else
                        <div
                            class="h-16 w-16 rounded-full bg-linear-to-br from-[#a17f8f] to-[#7a5466] flex items-center justify-center text-white font-bold text-2xl border-2 border-gray-200">
                            {{ strtoupper(substr($appeal->user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 break-words">{{ $appeal->user->name }}
                    </h3>
                    <p class="text-gray-600 text-sm sm:text-base">@<span>{{ $appeal->user->username }}</span></p>
                </div>
            </div>
            <div class="text-left sm:text-right flex flex-row sm:flex-col items-center sm:items-end gap-2 sm:gap-0">
                <x-ui.badge :variant="$appeal->status === 'approved' ? 'online' : ($appeal->status === 'rejected' ? 'offline' : 'pending')" size="xs" :icon="$appeal->status === 'approved' ? 'fas fa-check' : ($appeal->status === 'rejected' ? 'fas fa-times' : 'fas fa-clock')">
                    {{ ucfirst($appeal->status) }}
                </x-ui.badge>
                <p class="text-xs sm:text-sm text-gray-500 mt-0 sm:mt-2">
                    @if ($appeal->createdat instanceof \Carbon\Carbon)
                        {{ $appeal->createdat->diffForHumans() }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>

        <div class="mb-4 sm:mb-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                <i class="fas fa-comment-dots mr-2 text-[#a17f8f]"></i>
                Reason for Appeal:
            </h4>
            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 border border-gray-200">
                <p class="text-gray-800 whitespace-pre-wrap text-sm sm:text-base">{{ $appeal->reason }}</p>
            </div>
        </div>

        <div class="mb-4 sm:mb-6 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 text-center">
            <div class="bg-gray-50 rounded-lg p-2 sm:p-3">
                <p class="text-xs text-gray-500 mb-1">User ID</p>
                <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $appeal->user->id }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 sm:p-3">
                <p class="text-xs text-gray-500 mb-1">Member Since</p>
                <p class="text-base sm:text-lg font-semibold text-gray-900">
                    @if ($appeal->user->createdat)
                        {{ \Carbon\Carbon::parse($appeal->user->createdat)->format('M Y') }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>

        @if ($appeal->status === 'pending')
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <x-ui.button variant="success" onclick="approveAppeal({{ $appeal->id }})" class="flex-1">
                    <i class="fas fa-check-circle mr-2"></i>
                    Approve & Unblock
                </x-ui.button>
                <x-ui.button variant="danger" onclick="rejectAppeal({{ $appeal->id }})" class="flex-1">
                    <i class="fas fa-times-circle mr-2"></i>
                    Reject Appeal
                </x-ui.button>
            </div>
        @endif
    </div>
</div>
