@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-12">

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl relative mb-10 text-lg" role="alert">
                <span class="block sm:inline"><i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Groups</h1>
                <p class="text-gray-500 mt-2 text-base font-normal">Discover communities and join the conversation.</p>
            </div>
            <x-ui.button href="{{ route('groups.create') }}" variant="primary" class="shadow-md bg-[#820263] hover:bg-[#600149] py-3 px-6 text-base font-bold">
                <i class="fas fa-plus mr-2"></i> Create Group
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer block">

                    <div class="h-24 bg-linear-to-br from-gray-100 via-gray-200 to-gray-300 relative">
                        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    </div>

                    <div class="px-6 pb-6 flex-1 flex flex-col relative">

                        <div class="-mt-12 mb-4">
                            <div class="bg-white p-2 rounded-2xl shadow-sm inline-block">
                                <div class="h-20 w-20 bg-[#820263] rounded-xl flex items-center justify-center text-white text-3xl font-extrabold shadow-inner">
                                    {{ substr($group->name, 0, 1) }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h2 class="text-2xl font-black text-gray-900 leading-tight mb-3 group-hover:text-[#820263] transition-colors">
                                {{ $group->name }}
                            </h2>
                            
                            <div class="flex items-center gap-3">
                                @if($group->isprivate)
                                    <span class="inline-flex items-center bg-amber-50 text-amber-700 text-sm px-3 py-1 rounded-full font-bold border border-amber-200">
                                        <i class="fas fa-lock mr-1.5"></i> Private
                                    </span>
                                @else
                                    <span class="inline-flex items-center bg-green-50 text-green-700 text-sm px-3 py-1 rounded-full font-bold border border-green-200">
                                        <i class="fas fa-globe mr-1.5"></i> Public
                                    </span>
                                @endif
                                
                                @if(isset($group->is_member) && $group->is_member)
                                    <span class="inline-flex items-center bg-purple-100 text-[#820263] text-sm px-3 py-1 rounded-full font-bold border border-purple-200">
                                        <i class="fas fa-check mr-1.5"></i> Joined
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-gray-700 mb-6 text-base leading-relaxed line-clamp-3 flex-1">
                            {{ $group->description }}
                        </p>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex items-center text-gray-600 text-sm font-medium">
                                <i class="fas fa-users mr-2 text-gray-400"></i>
                                {{ $group->users_count ?? 0 }} Members
                            </div>
                            
                            <span class="text-[#820263] font-bold text-sm group-hover:underline flex items-center">
                                View Group <i class="fas fa-arrow-right ml-2 text-sm transition-transform group-hover:translate-x-1"></i>
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-24 text-center">
                    <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-50 mb-10 text-gray-300">
                        <i class="fas fa-layer-group text-7xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-4">No groups found</h3>
                    <p class="text-gray-500 mb-12 max-w-lg mx-auto text-2xl">There are no groups created yet. Be the pioneer and start the first community!</p>
                    <x-ui.button href="{{ route('groups.create') }}" variant="primary" class="shadow-md bg-[#820263] hover:bg-[#600149] py-5 px-12 text-2xl font-bold">
                        <i class="fas fa-plus mr-3"></i> Create First Group
                    </x-ui.button>
                </div>
            @endforelse
        </div>
    </div>
@endsection