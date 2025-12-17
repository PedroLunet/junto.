@props([
    'type' => 'success', // 'success' or 'error'
    'title' => '',
    'message' => '',
    'dismissible' => true,
    'id' => null,
])

@php
    $isSuccess = $type === 'success';
    $bg = $isSuccess ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    $iconBg = $isSuccess ? 'bg-green-200' : 'bg-red-200';
    $iconColor = $isSuccess ? 'text-green-600' : 'text-red-600';
    $icon = $isSuccess
        ? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" class="stroke-green-400 fill-green-50"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" class="stroke-green-600"/></svg>'
        : '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" class="stroke-red-400 fill-red-50"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-6 6m0-6l6 6" class="stroke-red-600"/></svg>';
@endphp

<div @if($id) id="{{ $id }}" @endif class="relative flex items-start gap-4 p-6 rounded-2xl border {{ $bg }} shadow-sm mb-4">
    <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full {{ $iconBg }}">
        {!! $icon !!}
    </div>
    <div class="flex-1">
        <div class="font-semibold text-xl {{ $iconColor }} mb-1">{{ $title }}</div>
        <div class="text-gray-700 text-2xl">{{ $message }}</div>
    </div>
    @if($dismissible)
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.closest('div').style.display='none'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
