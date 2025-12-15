@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Centered container matching the Feed width --}}
    <div class="max-w-4xl mx-auto">
        
        {{-- Back Link --}}
        <div class="mb-6">
            <a href="{{ route('groups.show', $group->id) }}" class="text-gray-500 hover:text-[#820263] flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Group
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-8 border-b border-gray-100">
                <h1 class="text-2xl font-bold text-gray-800">Edit Group</h1>
                <p class="text-gray-600 mt-1">Update your group's information below.</p>
            </div>
            
            <div class="p-8">
                <form method="POST" action="{{ route('groups.update', $group->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 font-semibold mb-2">Group Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required
                            placeholder="e.g. Hiking Enthusiasts"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#820263] focus:border-transparent transition-all">
                        @error('name')
                            <div class="text-red-500 mt-2 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
                        <textarea name="description" id="description" rows="4"
                                placeholder="What is this group about?"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#820263] focus:border-transparent transition-all">{{ old('description', $group->description) }}</textarea>
                        @error('description')
                            <div class="text-red-500 mt-2 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <label class="flex items-start cursor-pointer">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="isPrivate" id="isPrivate" value="1" {{ old('isPrivate', $group->isprivate) ? 'checked' : '' }}
                                    class="form-checkbox h-5 w-5 text-[#820263] focus:ring-[#820263] rounded border-gray-300">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="block font-semibold text-gray-700">Make this group private</span>
                                <span class="block text-gray-500 mt-1">Private groups are not listed in the public directory unless searched for explicitly.</span>
                            </div>
                        </label>
                        @error('isPrivate')
                            <div class="text-red-500 mt-2 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end items-center gap-4">
                        <a href="{{ route('groups.show', $group->id) }}" class="text-gray-600 hover:text-gray-800 font-medium">Cancel</a>
                        <x-ui.button type="submit" variant="primary">Save Changes</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
