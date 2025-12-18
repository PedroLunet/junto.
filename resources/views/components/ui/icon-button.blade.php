@php
    $base = 'p-2 rounded-lg focus:outline-none focus:ring transition';
    $variants = [
        'blue' => 'text-blue-600 hover:bg-blue-50',
        'yellow' => 'text-yellow-600 hover:bg-yellow-50',
        'red' => 'text-red-600 hover:bg-red-50',
        'gray' => 'text-gray-600 hover:bg-gray-100',
    ];
    $variant = $attributes->get('variant', 'gray');
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['gray']);
@endphp

<button {{ $attributes->merge(['class' => $classes])->except('variant') }}>
    {{ $slot }}
</button>
