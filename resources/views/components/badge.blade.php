@php
    $variant = $attributes->get('variant', 'default');
    $size = $attributes->get('size', 'md');

    $baseClasses = 'inline-flex items-center font-medium rounded-full';

    $variants = [
        'connected' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'online' => 'bg-green-50 text-green-700 border border-green-200',
        'offline' => 'bg-red-50 text-red-700 border border-red-200',
        'disabled' => 'bg-gray-50 text-gray-500 border border-gray-200',
        'pending' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'default' => 'bg-gray-50 text-gray-700 border border-gray-200',
    ];

    $sizes = [
        'sm' => 'px-2 py-1 text-base',
        'md' => 'px-3 py-1.5 text-lg',
        'lg' => 'px-4 py-2 text-2xl',
    ];

    $variantClass = $variants[$variant] ?? $variants['default'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $classes = $baseClasses . ' ' . $variantClass . ' ' . $sizeClass;
@endphp

<span {{ $attributes->merge(['class' => $classes])->except(['variant', 'size']) }}>
    @if ($attributes->get('icon'))
        <i class="{{ $attributes->get('icon') }} mr-2 shrink-0 align-middle"></i>
    @endif
    {{ $slot }}
</span>
