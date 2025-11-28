@php
    $showUnfriendButton = $attributes->get('showUnfriendButton', false);
    $unfriendRoute = $attributes->get('unfriendRoute', '');
    $confirmMessage = $attributes->get('confirmMessage', 'Are you sure?');
    $showBefriendButton = $attributes->get('showBefriendButton', false);
    $friendButtonData = $attributes->get('friendButtonData', null);
@endphp

<div
    class="bg-white shadow-sm rounded-3xl overflow-hidden hover:shadow-md transition-shadow border border-gray-100 w-full">
    <div class="flex items-center justify-between p-6">
        <a href="{{ route('profile.show', $user->username) }}" class="flex items-center space-x-4 flex-1">
            <div class="w-32 h-32 rounded-full bg-[#F1EBF4] flex items-center justify-center shrink-0">
                <span class="text-4xl font-bold text-[#820273]">{{ substr($user->name, 0, 1) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-3xl text-gray-900 truncate">{{ $user->name }}</h3>
                <p class="text-gray-500 text-2xl truncate">@<!-- -->{{ $user->username }}</p>
            </div>
        </a>

        @if ($showUnfriendButton && $unfriendRoute)
            <form action="{{ $unfriendRoute }}" method="POST" onsubmit="return confirm('{{ $confirmMessage }}')"
                class="ml-4">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" class="px-4 py-1.5 text-2xl">
                    Unfriend
                    </x-button>
            </form>
        @elseif ($showBefriendButton && $user->id !== auth()->id() && $friendButtonData)
            <div class="ml-4">
                <x-profile.friend-button :user="$user" :friendButtonData="$friendButtonData" />
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
