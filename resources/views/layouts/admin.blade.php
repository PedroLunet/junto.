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
            <h1><a href="{{ route('admin.dashboard') }}" class="text-4xl font-bold">junto.</a></h1>
        </div>

        <nav class="flex-1 px-4">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-[#7455ad]' : '' }}">
                        Admin Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}"
                        class="block py-2 px-4 rounded hover:bg-[#7455ad] hover:text-white {{ request()->routeIs('admin.users') ? 'bg-[#7455ad]' : '' }}">
                        Users
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-4 border-t border-gray-700">
            <div class="text-gray-300 mb-2">{{ Auth::user()->name }} (Admin)</div>
            <a href="{{ url('/logout') }}" class="text-red-400 hover:text-red-300 text-xl">Logout</a>
        </div>
    </aside>

    <!-- main content -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 sticky top-0 z-10 mb-0 flex justify-between items-center">
            <h2 class="font-semibold">@yield('page-title', 'Admin Panel')</h2>
        </header>

        <section class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </section>
    </main>
</body>

</html>
