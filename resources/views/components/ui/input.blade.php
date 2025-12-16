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
    'rows' => 4,
])

@php
    $inputId = $name . '-' . uniqid();
    $isTextarea = $type === 'textarea';
@endphp

<div class="mb-6">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-2xl font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>
    @endif

    <div class="relative flex items-center">
        @if ($isTextarea)
            <textarea id="{{ $inputId }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $attributes->merge(['class' => 'w-full px-4 py-3 text-2xl text-gray-800 border rounded-lg outline-none transition-all duration-200 placeholder:text-gray-400 focus:border-[#820273] focus:ring-4 focus:ring-purple-100 resize-none ' . ($error ? 'border-red-500 pr-12 focus:border-red-500 focus:ring-red-100' : 'border-gray-300')]) }}>{{ old($name, $value) }}</textarea>
        @else
            <input type="{{ $type }}" id="{{ $inputId }}" name="{{ $name }}"
                value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
                {{ $attributes->merge(['class' => 'w-full px-4 py-3 text-2xl text-gray-800 border rounded-lg outline-none transition-all duration-200 placeholder:text-gray-400 focus:border-[#820273] focus:ring-4 focus:ring-purple-100 ' . ($error ? 'border-red-500 pr-12 focus:border-red-500 focus:ring-red-100' : 'border-gray-300')]) }} />
        @endif

        @if ($type === 'password')
            <button type="button"
                class="absolute right-4 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors duration-200"
                onclick="togglePassword('{{ $inputId }}', this)">
                <i class="fas fa-eye"></i>
            </button>
        @elseif ($icon && !$error && !$isTextarea)
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
            <span
                class="absolute right-4 {{ $isTextarea ? 'top-4' : '' }} flex items-center justify-center pointer-events-none">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            </span>
        @endif
    </div>

    @if ($error)
        <p class="mt-2 text-sm text-red-500 font-medium">{{ $error }}</p>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function togglePassword(inputId, button) {
                const input = document.getElementById(inputId);
                const icon = button.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        </script>
    @endpush
@endonce
