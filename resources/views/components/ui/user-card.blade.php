@php
    $showUnfriendButton = $attributes->get('showUnfriendButton', false);
    $unfriendRoute = $attributes->get('unfriendRoute', '');
    $confirmMessage = $attributes->get('confirmMessage', 'Are you sure?');
    $showBefriendButton = $attributes->get('showBefriendButton', false);
    $friendButtonData = $attributes->get('friendButtonData', null);
@endphp

<div
    class="bg-white shadow-sm rounded-3xl overflow-hidden hover:shadow-md transition-shadow border border-gray-100 w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0 p-3 sm:p-4">
        <a href="{{ route('profile.show', $user->username) }}" class="flex items-center gap-3 flex-1 min-w-0">
            @if ($user->profilepicture)
                <img src="{{ asset('profile/' . $user->profilepicture) }}" alt="{{ $user->name }}"
                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover shrink-0"
                    onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
            @else
                <img src="{{ asset('profile/default.png') }}" alt="{{ $user->name }}"
                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover shrink-0">
            @endif
            <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-base sm:text-lg text-gray-900 truncate">{{ $user->name }}</h3>
                <p class="text-gray-500 text-sm sm:text-base truncate">@<span>{{ $user->username }}</span></p>
            </div>
        </a>

        @if (isset($actions))
            {{ $actions }}
        @else
            <div class="flex w-full sm:w-auto justify-end">
                @if ($showUnfriendButton && $unfriendRoute)
                    <form action="{{ $unfriendRoute }}" method="POST"
                        onsubmit="return confirm('{{ $confirmMessage }}')" class="mt-2 sm:mt-0 sm:ml-3">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" class="px-3 py-1 text-sm sm:text-base">
                            Unfriend
                        </x-ui.button>
                    </form>
                @elseif ($showBefriendButton && $user->id !== auth()->id() && $friendButtonData)
                    <div class="mt-2 sm:mt-0 sm:ml-3">
                        <x-profile.friend-button :user="$user" :friendButtonData="$friendButtonData" />
                    </div>
                @endif
            </div>
        @endif
    </div>
    {{ $slot }}
</div>
