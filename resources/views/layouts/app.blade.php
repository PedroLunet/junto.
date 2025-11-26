<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/milligram.css') }}">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @stack('styles')

        <!-- Scripts -->
        <script>
            window.isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
            window.currentUserUsername = "{{ auth()->check() ? auth()->user()->username : '' }}";
        </script>
        <script src="{{ asset('js/app.js') }}" defer></script>
        @stack('scripts')
    </head>
   <body class="flex h-screen">
        
        <!-- sidebar -->
        <aside class="w-80 bg-[#38157a] text-white flex flex-col">
            <div class="p-4">
                <h1><a href="/" class="text-4xl font-bold">junto.</a></h1>
            </div>
            
            <nav class="flex-1 px-4">
                @auth
                <ul class="space-y-2">
                    <li><a href="#" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Friends Feed</a></li>
                    <li><a href="{{ route('movies') }}" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Movies</a></li>
                    <li><a href="#" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Books</a></li>
                    <li><a href="#" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Music</a></li>
                </ul>
                @endauth
            </nav>

    

            @auth
            <div class="px-4 mb-4 flex flex-col gap-2">
                <button id="regular-button" class="w-full bg-[#7455ad] hover:bg-[#5a3d8a] text-white py-2 rounded-lg text-3xl transition-all duration-200 hover:scale-105"> + </button>
                <div class="flex gap-2">
                    <button id="movie-button" class="flex-1 bg-[#7455ad] hover:bg-[#5a3d8a] text-white py-2 rounded-lg transition-all duration-200 hover:scale-105 flex justify-center items-center">
                        <i class="fa-solid fa-clapperboard text-2xl"></i>
                    </button>
                    <button id="book-button" class="flex-1 bg-[#7455ad] hover:bg-[#5a3d8a] text-white py-2 rounded-lg transition-all duration-200 hover:scale-105 flex justify-center items-center">
                        <i class="fa-solid fa-book text-2xl"></i>
                    </button>
                    <button id="music-button" class="flex-1 bg-[#7455ad] hover:bg-[#5a3d8a] text-white py-2 rounded-lg transition-all duration-200 hover:scale-105 flex justify-center items-center">
                        <i class="fa-solid fa-music text-2xl"></i>
                    </button>
                </div>
            </div>
            @endauth
            
            @auth
                <div class="p-4 border-t border-gray-700">
                    <a href="{{ route('profile.show', Auth::user()->username) }}" class=" text-gray-300 mb-2">{{ Auth::user()->name }}</a>
                    <a href="{{ url('/logout') }}" class="text-red-400 hover:text-red-300 text-xl">Logout</a>
                </div>
            @endauth
        </aside>

        <!-- main content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 sticky top-0 z-10 mb-0 flex justify-between items-center">
                <h2 class="font-semibold">@yield('page-title', 'Home')</h2>
                @guest
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="bg-[#38157a] text-white px-4 py-2 rounded-lg hover:bg-[#7455ad] hover:text-white">Login</a>
                        <a href="{{ route('register') }}" class="bg-white text-[#38157a] px-4 py-2 border-[#38157a] border rounded-lg">Register</a>
                    </div>
                @endguest
            </header>
            
            <section class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </section>
        </main>


        @auth
            <x-create-regular-modal/>
            <x-create-movie-review-modal/>
            <x-create-book-review-modal/>
            <x-create-music-review-modal/>
        @endauth
    </body>
</html>