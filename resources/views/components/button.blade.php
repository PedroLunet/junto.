@php
    $base = 'px-4 py-2 rounded font-semibold focus:outline-none focus:ring transition';
    $variants = [
        'primary' => 'bg-purple-600 text-white hover:bg-purple-700',
        'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ];
    $variant = $attributes->get('variant', 'primary');
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp
<button {{ $attributes->merge(['class' => $classes])->except('variant') }}>
    {{ $slot }}
</button>
