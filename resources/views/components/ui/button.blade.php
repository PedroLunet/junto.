@php
    $base = 'rounded-[14px] focus:outline-none focus:ring transition border-zinc-400 border-[0.8px]';
    $variants = [
        'primary' => 'px-8 py-2 text-white opacity-90',
        'special' => 'px-2 py-3 bg-stone-300 text-[#291720] text-center font-bold text-lg',
        'secondary' => 'px-8 py-2 bg-transparent',
        'danger' => 'px-8 py-2 bg-red-600 text-white hover:bg-red-700',
        'success' => 'px-8 py-2 bg-green-600 text-white hover:bg-green-700',
        'ghost' => 'px-4 py-2 border-none bg-transparent hover:bg-gray-200 hover:opacity-90',
    ];
    $variant = $attributes->get('variant', 'primary');
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
    $href = $attributes->get('href');
    $tag = $href ? 'a' : 'button';
@endphp
@php
    $defaultStyle = '';
    if ($variant === 'primary') {
        $defaultStyle = 'background-image: linear-gradient(45deg, #F75C03 0%, #820263 50%, #291720 100%);';
    } elseif ($variant === 'special') {
        $defaultStyle = 'box-shadow: -2px 4px 8px 0px rgba(0,0,0,0.25), 2px -2px 4px 2px rgba(130,2,99,0.2) inset, -2px 2px 4px 2px rgba(247,92,3,0.3) inset, -2px -2px 2px 0px rgba(0,0,0,0.3) inset, 2px 2px 4px 0px rgba(141,141,141,0.6) inset;';
    }
    $userStyle = $attributes->get('style');
    $finalStyle = $defaultStyle;
    if ($userStyle) {
        $finalStyle = $defaultStyle . ' ' . $userStyle;
    }
@endphp
<{{ $tag }}
    @if($finalStyle)
        style="{{ $finalStyle }}"
    @endif
    {{ $attributes->merge(['class' => $classes])->except('variant')->except('style') }}>
    {{ $slot }}
</{{ $tag }}>
