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
    'disabled' => false,
    'id' => null,
    'help' => '',
])

@php
    $inputId = $id ?? $name . '-' . uniqid();
    $isTextarea = $type === 'textarea';
    $isEmail = $type === 'email';
    $emailPattern = $isEmail ? '[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}' : null;
@endphp

<div class="mb-4 {{ $help ? 'overflow-visible' : '' }}">
    @if ($label)
        <div class="flex items-center gap-2 mb-2">
            <label for="{{ $inputId }}"
                class="block text-base sm:text-lg md:text-xl lg:text-lg font-medium text-gray-700">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
            @if ($help)
                <div class="group relative flex items-center ml-1" onmouseenter="positionTooltip(this)" onmouseleave="hideTooltip(this)">
                    <i class="fas fa-question-circle text-gray-400 hover:text-[#820263] cursor-help text-sm transition-colors"></i>
                    <div
                        class="tooltip-content fixed invisible opacity-0 transition-opacity duration-200 w-80 max-w-[calc(100vw-2rem)] p-4 bg-[#291720] text-white text-sm leading-relaxed font-normal rounded-xl shadow-2xl z-[9999] text-left pointer-events-none whitespace-normal">
                        {{ $help }}
                        <div
                            class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-[#291720]">
                        </div>
                    </div>
                </div>
                @once
                <script>
                    function positionTooltip(element) {
                        const tooltip = element.querySelector('.tooltip-content');
                        const icon = element.querySelector('i');
                        const rect = icon.getBoundingClientRect();
                        
                        tooltip.style.left = rect.left + rect.width / 2 + 'px';
                        tooltip.style.top = rect.top - 12 + 'px';
                        tooltip.style.transform = 'translate(-50%, -100%)';
                        
                        tooltip.classList.remove('invisible');
                        tooltip.classList.add('opacity-100');
                    }
                    
                    function hideTooltip(element) {
                        const tooltip = element.querySelector('.tooltip-content');
                        tooltip.classList.add('invisible');
                        tooltip.classList.remove('opacity-100');
                    }
                </script>
                @endonce
            @endif
        </div>
    @endif

    <div class="relative flex items-center">
        @if ($isTextarea)
            <textarea id="{{ $inputId }}" name="{{ $name }}" rows="{{ $rows }}"
                placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }} {{ $disabled ? 'disabled' : '' }}
                {{ $attributes->merge(['class' => 'w-full px-3 sm:px-4 py-2 sm:py-3 text-sm text-gray-800 border rounded-lg outline-none transition-all duration-200 placeholder:text-gray-400 focus:border-[#820273] focus:ring-4 focus:ring-purple-100 resize-none ' . ($disabled ? 'bg-gray-400 cursor-not-allowed' : '') . ' ' . ($error ? 'border-red-500 pr-12 focus:border-red-500 focus:ring-red-100' : 'border-gray-300')]) }}>{{ old($name, $value) }}</textarea>
        @else
            <input type="{{ $type }}" id="{{ $inputId }}" name="{{ $name }}"
                value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                @if ($isEmail) pattern="{{ $emailPattern }}" title="Please enter a valid email address" @endif
                {{ $attributes->merge(['class' => 'w-full px-3 sm:px-4 py-2 sm:py-3 text-base sm:text-lg md:text-xl lg:text-base text-gray-800 border rounded-lg outline-none transition-all duration-200 placeholder:text-gray-400 focus:border-[#820273] focus:ring-4 focus:ring-purple-100 ' . ($disabled ? 'bg-gray-400 cursor-not-allowed' : '') . ' ' . ($error ? 'border-red-500 pr-12 focus:border-red-500 focus:ring-red-100' : 'border-gray-300')]) }} />
        @endif

        @if ($type === 'password' && !$disabled)
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
                <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
            </span>
        @endif
    </div>

    @if ($error)
        <p class="mt-1 text-xs text-red-500 font-medium">{{ $error }}</p>
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

            // Email validation error handling
            document.addEventListener('DOMContentLoaded', function() {
                const emailInputs = document.querySelectorAll('input[type="email"]');

                emailInputs.forEach(input => {
                    input.addEventListener('invalid', function(e) {
                        e.preventDefault();

                        if (input.validity.patternMismatch || input.validity.typeMismatch) {
                            // error styling
                            input.classList.remove('border-gray-300');
                            input.classList.add('border-red-500', 'focus:border-red-500',
                                'focus:ring-red-100', 'pr-12', '!border-red-500');

                            // error icon if not already present
                            if (!input.parentElement.querySelector('.input-error-icon')) {
                                const iconHtml = `<span class="absolute right-4 flex items-center justify-center pointer-events-none input-error-icon">
                                    <i class="fas fa-exclamation-circle text-red-500 text-3xl"></i>
                                </span>`;
                                input.parentElement.insertAdjacentHTML('beforeend', iconHtml);
                            }

                            // error message if not already present
                            if (!input.parentElement.parentElement.querySelector(
                                    '.input-error-message')) {
                                const errorMsg =
                                    `<p class="mt-2 text-xl text-red-500 font-medium input-error-message">Please enter a valid email address</p>`;
                                input.parentElement.parentElement.insertAdjacentHTML('beforeend',
                                    errorMsg);
                            }
                        }
                    });

                    // clear error
                    input.addEventListener('input', function() {
                        if (input.validity.valid) {
                            input.classList.remove('border-red-500', 'focus:border-red-500',
                                'focus:ring-red-100', 'pr-12', '!border-red-500');
                            input.classList.add('border-gray-300');

                            const errorIcon = input.parentElement.querySelector('.input-error-icon');
                            if (errorIcon) errorIcon.remove();

                            const errorMessage = input.parentElement.parentElement.querySelector(
                                '.input-error-message');
                            if (errorMessage) errorMessage.remove();
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
