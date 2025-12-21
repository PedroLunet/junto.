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

        @hasSection('header-scripts')
            @yield('header-scripts')
        @endif
    </script>
</head>

<body class="flex h-screen bg-[#F1EBF4] overflow-hidden">

    <x-ui.button id="mobile-menu-button" variant="ghost" onclick="toggleMobileMenu()"
        class="lg:hidden fixed top-4 left-4 z-50">
        <i class="fa-solid fa-bars text-xl"></i>
    </x-ui.button>

    <div id="mobile-overlay" class="hidden lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"
        onclick="toggleMobileMenu()"></div>

    <!-- sidebar -->
    <aside id="sidebar"
        class="fixed lg:relative w-64 lg:w-1/6 h-full bg-[#624452] text-white flex flex-col rounded-r-2xl shadow-2xl z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div class="p-6 flex flex-col xl:flex-row justify-between items-center gap-4">
            <h1><a href="/" class="text-3xl font-bold hover:text-[#a17f8f]">junto.</a></h1>
            <div class="flex items-center justify-end">
                @yield('header-actions')
            </div>
        </div>

        <nav class="flex-1 px-4">
            @yield('navigation')
        </nav>

        @hasSection('sidebar-actions')
            <div class="px-4 mb-4 flex flex-col gap-2">
                @yield('sidebar-actions')
            </div>
        @endif

        @auth
            <div class="p-4 border-t border-[#7a5466] flex items-center justify-between gap-2">
                <a href="{{ route('profile.show', Auth::user()->username) }}" class="flex items-center gap-2 flex-1 min-w-0">
                    @if (Auth::user()->profilepicture)
                        <img src="{{ asset('profile/' . Auth::user()->profilepicture) }}" alt="{{ Auth::user()->name }}"
                            class="w-10 h-10 rounded-full object-cover shrink-0"
                            onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
                    @else
                        <img src="{{ asset('profile/default.png') }}" alt="{{ Auth::user()->name }}"
                            class="w-10 h-10 rounded-full object-cover shrink-0">
                    @endif
                    <div class="flex flex-col min-w-0">
                        <span class="text-white font-semibold text-sm truncate">{{ Auth::user()->name }}</span>
                        <span class="text-gray-400 text-xs truncate">@<span>{{ Auth::user()->username }}</span></span>
                    </div>
                </a>
                <x-ui.button href="{{ url('/logout') }}" variant="ghost" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg text-red-500"></i>
                </x-ui.button>
            </div>

            <div class="p-4 flex justify-center gap-1 text-xs">
                <a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">About Us</a>
                <span class="text-gray-500">·</span>
                <a href="{{ route('features') }}" class="text-gray-300 hover:text-white transition-colors">Main
                    Features</a>
                <span class="text-gray-500">·</span>
                <a href="{{ route('contact.show') }}" class="text-gray-300 hover:text-white transition-colors">Contact
                    Us</a>
            </div>
        @else
            <div class="p-4 border-t border-gray-700 flex flex-col gap-2">
                <x-ui.button href="{{ route('login') }}" variant="primary" class="w-full text-center">Login</x-ui.button>
                <x-ui.button href="{{ route('register') }}" variant="secondary"
                    class="w-full text-center">Register</x-ui.button>
            </div>

            <div class="p-4 flex justify-center gap-1 text-xs">
                <a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">About Us</a>
                <span class="text-gray-500">·</span>
                <a href="{{ route('features') }}" class="text-gray-300 hover:text-white transition-colors">Main
                    Features</a>
                <span class="text-gray-500">·</span>
                <a href="{{ route('contact.show') }}" class="text-gray-300 hover:text-white transition-colors">Contact
                    Us</a>
            </div>
        @endauth
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

    @hasSection('modals')
        @yield('modals')
    @endif

    <x-ui.notification-alert />
</body>

</html>
