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

        function updateNotificationBadge() {
            if (!window.isAuthenticated) return;

            fetch('/notifications/unread-count', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        document.addEventListener('DOMContentLoaded', updateNotificationBadge);
        setInterval(updateNotificationBadge, 30000);
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
        <div class="p-8 flex lg:flex-row flex-col justify-between items-center">
            <h1><a href="/" class="text-4xl font-bold hover:text-[#a17f8f]">junto.</a></h1>
            <div class="flex items-center gap-2 w-full overflow-hidden justify-end">
                @auth
                    <x-ui.button href="{{ route('notifications.index') }}" variant="ghost" class="p-2 relative"
                        title="Inbox">
                        <i class="fa-solid fa-inbox text-2xl"></i>
                        <span id="notification-badge"
                            class="absolute top-0 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                    </x-ui.button>
                @endauth
                @guest
                    <x-ui.button href="{{ route('friend-requests.index') }}" variant="ghost" class="p-2" title="Inbox">
                        <i class="fa-solid fa-inbox text-2xl"></i>
                    </x-ui.button>
                @endguest
                <x-ui.button href="{{ route('search.users') }}" variant="ghost" class="p-2" title="Search">
                    <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                </x-ui.button>
                <button onclick="toggleMobileMenu()" class="lg:hidden text-white">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <nav class="flex-1 px-4">
            <ul class="space-y-2">
                <li><a href="{{ route('friends-feed') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white">Friends Feed</a></li>
                <li><a href="{{ route('movies') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white">Movies</a></li>
                <li><a href="{{ route('books') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white">Books</a></li>
                <li><a href="{{ route('music') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white">Music</a></li>

                <li><a href="{{ route('groups.index') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white">Groups</a></li>

                <li><a href="{{ route('about') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7a5466] hover:text-white">About Us</a></li>

            </ul>
        </nav>


        <div class="px-4 mb-4 flex flex-col gap-2">
            @auth
                <x-ui.button id="regular-button" variant="special"> + </x-ui.button>
            @else
                <x-ui.button href="{{ route('login') }}" variant="special"> + </x-ui.button>
            @endauth
            <div class="hidden lg:flex gap-2 w-full">
                @auth
                    <x-ui.button id="movie-button" variant="special" class="flex-1 justify-center">
                        <i class="fa-solid fa-clapperboard text-2xl"></i>
                    </x-ui.button>
                    <x-ui.button id="book-button" variant="special" class="flex-1 justify-center">
                        <i class="fa-solid fa-book text-2xl"></i>
                    </x-ui.button>
                    <x-ui.button id="music-button" variant="special" class="flex-1 justify-center">
                        <i class="fa-solid fa-music text-2xl"></i>
                    </x-ui.button>
                @else
                    <x-ui.button href="{{ route('login') }}" variant="special" class="flex-1 justify-center">
                        <i class="fa-solid fa-clapperboard text-2xl"></i>
                    </x-ui.button>
                    <x-ui.button href="{{ route('login') }}" variant="special" class="flex-1 justify-center">
                        <i class="fa-solid fa-book text-2xl"></i>
                    </x-ui.button>
                    <x-ui.button href="{{ route('login') }}" variant="special" class="flex-1 justify-center">
                        <i class="fa-solid fa-music text-2xl"></i>
                    </x-ui.button>
                @endauth
            </div>
        </div>

        @auth
            <div class="p-4 border-t border-[#7a5466] flex items-center justify-between">
                <a href="{{ route('profile.show', Auth::user()->username) }}" class="flex items-center gap-3 flex-1">
                    @if (Auth::user()->profilepicture)
                        <img src="{{ asset('profile/' . Auth::user()->profilepicture) }}" alt="{{ Auth::user()->name }}"
                            class="w-24 h-24 rounded-full object-cover">
                    @else
                        <img src="{{ asset('profile/default.png') }}" alt="{{ Auth::user()->name }}"
                            class="w-24 h-24 rounded-full object-cover">
                    @endif
                    <div class="flex flex-col ml-2">
                        <span class="text-white font-semibold text-2xl">{{ Auth::user()->name }}</span>
                        <span class="text-gray-400 text-xl">@<span>{{ Auth::user()->username }}</span></span>
                    </div>
                </a>
                <x-ui.button href="{{ url('/logout') }}" variant="ghost" class="p-2" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-xl text-red-500"></i>
                </x-ui.button>
            </div>
        @else
            <div class="p-4 border-t border-gray-700 flex flex-col gap-2">
                <x-ui.button href="{{ route('login') }}" variant="primary"
                    class="w-full text-center">Login</x-ui.button>
                <x-ui.button href="{{ route('register') }}" variant="secondary"
                    class="w-full text-center">Register</x-ui.button>
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


    @auth
        <x-posts.create.create-regular-modal />
        <x-posts.create.create-movie-review-modal />
        <x-posts.create.create-book-review-modal />
        <x-posts.create.create-music-review-modal />
    @endauth
</body>

</html>
