<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/milligram.css') }}">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @stack('styles')

        <!-- Scripts -->
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
                <ul class="space-y-2">
                    <li><a href="#" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Friends Feed</a></li>
                    <li><a href="{{ route('friends.index') }}" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">My Friends</a></li>
                    <li><a href="{{ route('friend-requests.index') }}" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Friend Requests</a></li>
                    <li><a href="{{ route('movies') }}" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Movies</a></li>
                    <li><a href="#" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Books</a></li>
                    <li><a href="#" class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white">Music</a></li>
                </ul>
            </nav>
            
            @auth
                <div class="p-4 border-t border-gray-700">
                    <a href="{{ route('profile.show', Auth::user()->username) }}" class=" text-gray-300 mb-2">{{ Auth::user()->name }}</a>
                    <a href="{{ url('/logout') }}" class="text-red-400 hover:text-red-300 text-xl">Logout</a>
                </div>
            @endauth
        </aside>

        <!-- main content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 sticky top-0 z-10 mb-0">
                <h2 class="font-semibold">@yield('page-title', 'Home')</h2>
            </header>
            
            <section class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </section>
        </main>
        
    </body>
</html>