@props([
    'icon' => 'fa-circle-question',
    'title' => 'No Items',
    'description' => 'There are no items to display',
    'height' => 'min-h-full',
])

<div class="flex flex-col items-center justify-center text-gray-500 {{ $height }}">
    <div class="text-center">
        <div class="bg-gray-200 rounded-full p-6 inline-block mb-4">
            <i class="fas {{ $icon }} text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-xl font-medium text-gray-700">{{ $title }}</h3>
        <p class="mt-2">{{ $description }}</p>
    </div>
</div>
