@props([
    'label' => '',
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'error' => '',
    'icon' => null,
    'iconAction' => null,
])

<div class="mb-6">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>
    @endif

    <div class="relative flex items-center">
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
            value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'w-full px-4 py-3 text-base text-gray-800 bg-white border-2 rounded-lg outline-none transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 ' . ($error ? 'border-red-500 pr-12 focus:border-red-500 focus:ring-red-100' : ($value ? 'border-blue-500' : 'border-gray-300'))]) }} />

        @if ($icon && !$error)
            @if ($iconAction)
                <button type="button"
                    class="absolute right-4 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    onclick="{{ $iconAction }}">
                    {!! $icon !!}
                </button>
            @else
                <span class="absolute right-4 flex items-center justify-center text-gray-400">
                    {!! $icon !!}
                </span>
            @endif
        @endif

        @if ($error)
            <span class="absolute right-4 flex items-center justify-center pointer-events-none">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            </span>
        @endif
    </div>

    @if ($error)
        <p class="mt-2 text-sm text-red-500 font-medium">{{ $error }}</p>
    @endif
</div>
