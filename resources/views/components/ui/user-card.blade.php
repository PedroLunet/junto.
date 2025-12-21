@php
    $showUnfriendButton = $attributes->get('showUnfriendButton', false);
    $unfriendRoute = $attributes->get('unfriendRoute', '');
    $confirmMessage = $attributes->get('confirmMessage', 'Are you sure?');
    $showBefriendButton = $attributes->get('showBefriendButton', false);
    $friendButtonData = $attributes->get('friendButtonData', null);
@endphp

<div
    class="bg-white shadow-sm rounded-3xl overflow-hidden hover:shadow-md transition-shadow border border-gray-100 w-full">
    <div class="flex items-center justify-between p-4">
        <a href="{{ route('profile.show', $user->username) }}" class="flex items-center space-x-3 flex-1">
            @if ($user->profilepicture)
                <img src="{{ asset('profile/' . $user->profilepicture) }}" alt="{{ $user->name }}"
                    class="w-16 h-16 rounded-full object-cover shrink-0"
                    onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
            @else
                <img src="{{ asset('profile/default.png') }}" alt="{{ $user->name }}"
                    class="w-16 h-16 rounded-full object-cover shrink-0">
            @endif
            <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-lg text-gray-900 truncate">{{ $user->name }}</h3>
                <p class="text-gray-500 text-base truncate">@<!-- -->{{ $user->username }}</p>
            </div>
        </a>

        @if ($showUnfriendButton && $unfriendRoute)
            <form action="{{ $unfriendRoute }}" method="POST" onsubmit="return confirm('{{ $confirmMessage }}')"
                class="ml-3">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" class="px-3 py-1 text-base">
                    Unfriend
                </x-ui.button>
            </form>
        @elseif ($showBefriendButton && $user->id !== auth()->id() && $friendButtonData)
            <div class="ml-3">
                <x-profile.friend-button :user="$user" :friendButtonData="$friendButtonData" />
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
