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

    <!-- Scripts -->
    <script>
        window.isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        window.currentUserUsername = "{{ auth()->check() ? auth()->user()->username : '' }}";
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</head>

<body class="bg-[#F1EBF4] min-h-screen">
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>

</html>
