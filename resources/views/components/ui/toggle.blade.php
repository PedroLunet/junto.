@props([
    'name' => '',
    'checked' => false,
])

<label class="relative inline-flex items-center cursor-pointer">
    <input type="checkbox" class="sr-only peer" name="{{ $name }}" {{ $checked ? 'checked' : '' }} {{ $attributes }}>
    <div
        class="w-14 h-8 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-100 rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#820273]">
    </div>
</label>
