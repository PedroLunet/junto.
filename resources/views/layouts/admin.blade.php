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
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const menuButton = document.getElementById('mobile-menu-button');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            menuButton.classList.toggle('hidden');
        }
    </script>
</head>

<body class="flex h-screen bg-[#F1EBF4] overflow-hidden">

    <x-button id="mobile-menu-button" variant="ghost" onclick="toggleMobileMenu()"
        class="lg:hidden fixed top-4 left-4 z-50">
        <i class="fa-solid fa-bars text-xl"></i>
    </x-button>

    <div id="mobile-overlay" class="hidden lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"
        onclick="toggleMobileMenu()"></div>

    <!-- sidebar -->
    <aside id="sidebar"
        class="fixed lg:relative w-64 lg:w-1/6 h-full bg-[#624452] text-white flex flex-col rounded-r-2xl shadow-2xl z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div class="p-8 flex lg:flex-row flex-col justify-between items-center">
            <h1><a href="/" class="text-4xl font-bold hover:text-[#a17f8f]">junto.</a></h1>
            <div class="flex items-center gap-2 w-full overflow-hidden justify-end">
                <button onclick="toggleMobileMenu()" class="lg:hidden text-white">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <nav class="flex-1 px-4">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="block py-2 px-4 rounded  hover:bg-[#7a5466] hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-[#a17f8f]' : '' }}">
                        Admin Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}"
                        class="block py-2 px-4 rounded  hover:bg-[#7a5466] hover:text-white {{ request()->routeIs('admin.users') ? 'bg-[#a17f8f]' : '' }}">
                        Users
                    </a>
                </li>             
            </ul>
        </nav>
    </aside>

    <!-- main content -->
    <main class="flex-1 flex flex-col overflow-hidden w-full lg:w-auto">
        @hasSection('title')
            <header class="bg-transparent shadow-sm p-4 sticky top-0 z-10 mb-0 flex justify-between items-center">
                <h2 class="text-[#624452] font-semibold ml-16 lg:ml-0">@yield('title')</h2>
            </header>
        @endif

        <section class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </section>
    </main>


    @auth
        <x-create-regular-modal />
        <x-create-movie-review-modal />
        <x-create-book-review-modal />
        <x-create-music-review-modal />
    @endauth
</body>

</html>
