@php
    $sizeClasses = match ($type) {
        'music' => 'w-36 h-36 md:w-42 md:h-42 lg:w-48 lg:h-48',
        default => 'w-24 h-36 md:w-28 md:h-42 lg:w-32 lg:h-48',
    };
@endphp

@if ($media || Auth::id() === $user->id)
    <div class="{{ $sizeClasses }} bg-gray-300 rounded-xl flex items-center justify-center relative"
        @if ($media) title="{{ $media->title }}{{ $media->creator ? ' - ' . $media->creator : '' }}" @endif>

        @if ($media)
            <!-- Remove button  -->
            @if (Auth::id() === $user->id)
                <x-ui.icon-button onclick="removeFavorite('{{ $type }}')" variant="red"
                    title="Remove favorite {{ ucfirst($type) }}"
                    class="bg-white absolute -top-3 -right-3 w-7 h-7 flex items-center justify-center z-10 px-0 py-0">
                    <i class="fa fa-trash w-4 h-4"></i>
                </x-ui.icon-button>
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
                title="Add your favorite {{ ucfirst($type) }}"
                class="w-full h-full text-gray-600 text-3xl md:text-4xl font-light hover:text-gray-800 hover:bg-gray-400 cursor-pointer px-0 py-0">
                +
            </x-ui.button>
        @endif
    </div>
@endif
