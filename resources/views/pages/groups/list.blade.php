@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Groups</h1>
            <x-ui.button href="{{ route('groups.create') }}" variant="primary">Create Group</x-ui.button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($groups as $group)
                <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform duration-300">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2">
                            <a href="{{ route('groups.show', $group) }}" class="text-gray-800 hover:text-[#820263]">{{ $group->name }}</a>
                        </h2>
                        <p class="text-gray-600">{{ Str::limit($group->description, 100) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

