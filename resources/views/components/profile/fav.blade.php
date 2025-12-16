@php
    $sizeClasses = match ($type) {
        'music' => 'w-48 h-56 md:w-56 md:h-56 lg:w-72 lg:h-72',
        default => 'w-36 h-54 md:w-40 md:h-60 lg:w-48 lg:h-72',
    };
@endphp

@if ($media || Auth::id() === $user->id)
    <div class="{{ $sizeClasses }} bg-gray-300 rounded-xl flex items-center justify-center relative"
        @if ($media) title="{{ $media->title }}{{ $media->creator ? ' - ' . $media->creator : '' }}" @endif>

        @if ($media)
            <!-- Remove button (only for own profile) -->
            @if (Auth::id() === $user->id)
                <x-ui.button onclick="removeFavorite('{{ $type }}')" variant="danger"
                    class="absolute -top-5 -right-5 w-10 h-10 rounded-full flex items-center justify-center text-3xl font-bold z-10 px-0 py-0">
                    -
                </x-ui.button>
            @endif

            <!-- Media image or title -->
            @if ($media->coverimage && filter_var($media->coverimage, FILTER_VALIDATE_URL))
                <img src="{{ $media->coverimage }}" alt="{{ $media->title }}"
                    class="w-full h-full object-cover rounded-xl">
            @else
                <span class="text-gray-600 text-2xl md:text-xl text-center px-2">
                    {{ $media->title }}
                </span>
            @endif
        @else
            <!-- Add button (only for own profile) -->
            <x-ui.button onclick="openAddFavModal('{{ $type }}')" variant="ghost"
                class="w-full h-full text-gray-600 text-5xl md:text-6xl lg:text-7xl font-light hover:text-gray-800 hover:bg-gray-400 cursor-pointer px-0 py-0">
                +
            </x-ui.button>
        @endif
    </div>
@endif
