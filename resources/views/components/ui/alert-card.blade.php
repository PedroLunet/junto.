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
    $alertId = $id ?? 'alert-card-' . uniqid();
@endphp

<div id="{{ $alertId }}"
    class="fixed top-6 right-6 z-50 flex items-start gap-3 px-4 py-4 rounded-2xl border {{ $bg }} shadow-sm mb-4 min-w-[280px] max-w-xs">
    <div class="shrink-0 flex items-center justify-center w-8 h-8 rounded-full {{ $iconBg }}">
        {!! str_replace(['w-6 h-6'], ['w-5 h-5'], $icon) !!}
    </div>
    <div class="flex-1">
        <div class="font-semibold text-base {{ $iconColor }} mb-0.5">{{ $title }}</div>
        <div class="text-gray-700 text-sm">{{ $message }}</div>
    </div>
    @if ($dismissible)
        <x-ui.icon-button variant="green" class="absolute top-2 right-2" type="button"
            onclick="this.closest('div').style.display='none'">
            <i class="fa fa-times w-5 h-5"></i>
        </x-ui.icon-button>
    @endif
</div>

<script>
    (function() {
        var alert = document.getElementById(@json($alertId));
        if (alert) {
            setTimeout(function() {
                alert.style.display = 'none';
            }, 5000);
        }
    })();
</script>
