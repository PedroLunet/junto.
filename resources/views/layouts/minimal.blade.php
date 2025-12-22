<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')

    <title>@yield('title', 'Junto')</title>

    <!-- Open Graph Tags -->
    <meta property="og:title" content="@yield('title', 'Junto')" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:description" content="@yield('description', 'Junto - Connect with friends and share your interests.')" />
    <meta property="og:site_name" content="Junto" />
    <meta property="og:image" content="@yield('og:image', asset('illustration-friends.svg'))" />

    <!-- Scripts -->
    <script>
        window.isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        window.currentUserUsername = "{{ auth()->check() ? auth()->user()->username : '' }}";
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</head>

<body class="bg-[#F1EBF4] min-h-screen">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-black focus:rounded-md focus:shadow-lg">
        Skip to content
    </a>
    <main id="main-content" class="container mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>

</html>
