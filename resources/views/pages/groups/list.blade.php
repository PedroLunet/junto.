@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        {{-- Error/Success Handling --}}
        @if (session('error'))
            <div class="max-w-4xl mx-auto bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="max-w-4xl mx-auto flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Groups</h1>
            <x-ui.button href="{{ route('groups.create') }}" variant="primary">
                <i class="fas fa-plus mr-2"></i> Create Group
            </x-ui.button>
        </div>

        {{-- Main Feed Area --}}
        <div class="max-w-4xl mx-auto space-y-6">
            @forelse ($groups as $group)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        {{-- Group Header --}}
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">
                                    <a href="{{ route('groups.show', $group) }}" class="hover:text-[#820263] transition-colors">
                                        {{ $group->name }}
                                    </a>
                                </h2>
                                <div class="flex items-center mt-1 text-sm text-gray-500">
                                    @if($group->isPrivate)
                                        <span class="flex items-center text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-lock mr-1.5 text-xs"></i> Private Group
                                        </span>
                                    @else
                                        <span class="flex items-center text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-globe mr-1.5 text-xs"></i> Public Group
                                        </span>
                                    @endif
                                    <span class="mx-2">•</span>
                                    <span>Created {{ $group->created_at ? $group->created_at->diffForHumans() : '' }}</span>
                                </div>
                            </div>
                            
                            {{-- Action Button --}}
                            <x-ui.button href="{{ route('groups.show', $group) }}" variant="secondary" class="text-sm">
                                View Group
                            </x-ui.button>
                        </div>

                        {{-- Group Body --}}
                        <div class="text-gray-700 leading-relaxed">
                            {{ Str::limit($group->description, 180) }}
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty State (Matching your Friends Feed style) --}}
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-users text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No groups found</h3>
                    <p class="text-gray-600 mb-6">There are no groups yet. Why not start the first community?</p>
                    <x-ui.button href="{{ route('groups.create') }}" variant="primary">
                        Create New Group
                    </x-ui.button>
                </div>
            @endforelse
        </div>
    </div>
@endsection