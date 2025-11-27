@props(['title', 'value'])

<div class="bg-white rounded-3xl shadow p-10 text-center">
    <p class="text-lg font-medium text-gray-500 uppercase tracking-wide mb-3">{{ $title }}</p>
    <p class="text-6xl font-bold text-gray-900">{{ number_format($value) }}</p>
</div>
