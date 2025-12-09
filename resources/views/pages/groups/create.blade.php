@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md">
        <div class="p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Create a New Group</h1>

            <form method="POST" action="{{ route('groups.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-semibold mb-2">Group Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#820263]">
                    @error('name')
                        <div class="text-red-500 mt-2 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#820263]">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-red-500 mt-2 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="isPrivate" id="isPrivate" value="1" {{ old('isPrivate') ? 'checked' : '' }}
                               class="form-checkbox h-5 w-5 text-[#820263] focus:ring-[#820263]">
                        <span class="ml-2 text-gray-700">Make this group private</span>
                    </label>
                    @error('isPrivate')
                        <div class="text-red-500 mt-2 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <x-ui.button type="submit" variant="primary">Create Group</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

