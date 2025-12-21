@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-full py-12 px-4">
        <div class="max-w-2xl w-full text-center space-y-8">
            <!-- Header -->
            <div class="space-y-4">
                <p class="text-gray-500 text-lg">You look a little lost...</p>
                <h1 class="text-5xl md:text-6xl font-black text-gray-900">Ooops! Page not found</h1>
                <p class="text-gray-600 text-lg max-w-xl mx-auto">
                    This page seems to have wandered off like a plot twist you didn't see coming. 
                    Let's get you back to discovering great content!
                </p>
            </div>

            <!-- Illustration -->
            <div class="py-8">
                <div class="text-9xl flex items-center justify-center gap-4">
                    🎬 📚 🎵
                </div>
                <p class="text-gray-600 text-lg mt-4">Error 404</p>
            </div>

            <!-- Navigation Cards -->
            <div class="space-y-4 max-w-md mx-auto">
                <a href="/" 
                   class="block bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#820263] transition-all p-6 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-[#820263]/10 transition-colors">
                                <i class="fas fa-home text-xl text-gray-600 group-hover:text-[#820263]"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-gray-900 text-lg">Home</h3>
                                <p class="text-gray-500 text-sm">There's no place like home...</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-[#820263]"></i>
                    </div>
                </a>

                <a href="{{ route('friends-feed') }}" 
                   class="block bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#820263] transition-all p-6 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-[#820263]/10 transition-colors">
                                <i class="fas fa-user-group text-xl text-gray-600 group-hover:text-[#820263]"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-gray-900 text-lg">Friends Feed</h3>
                                <p class="text-gray-500 text-sm">See what your friends are doing</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-[#820263]"></i>
                    </div>
                </a>

                <a href="{{ route('groups.index') }}" 
                   class="block bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-md hover:border-[#820263] transition-all p-6 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-[#820263]/10 transition-colors">
                                <i class="fas fa-people-group text-xl text-gray-600 group-hover:text-[#820263]"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-gray-900 text-lg">Groups</h3>
                                <p class="text-gray-500 text-sm">Join communities of enthusiasts</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-[#820263]"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
