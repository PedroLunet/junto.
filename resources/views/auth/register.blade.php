@extends('pages.home')

@section('modal-overlay')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative overflow-hidden">
            <div class="px-10 pt-10 pb-4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-4xl font-bold text-gray-800 m-0">Create Account</h2>
                    <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block font-medium text-gray-700 mb-1">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            autocomplete="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800">
                        @error('name')
                            <span id="name-error" class="text-red-500 text-sm mt-1 block"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="username" class="block font-medium text-gray-700 mb-1">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            autocomplete="username"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800">
                        @error('username')
                            <span id="username-error" class="text-red-500 text-sm mt-1 block"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block font-medium text-gray-700 mb-1">E-mail Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" inputmode="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800">
                        @error('email')
                            <span id="email-error" class="text-red-500 text-sm mt-1 block"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800">
                        @error('password')
                            <span id="password-error" class="text-red-500 text-sm mt-1 block"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password-confirm" class="block font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input id="password-confirm" type="password" name="password_confirmation" required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800">
                    </div>

                    <div class="flex flex-col gap-6 items-center">
                        <x-ui.button type="submit" variant="primary">Register</x-ui.button>
                        <a href="{{ route('google-auth') }}"
                            class="flex items-center justify-center gap-3 bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-normal rounded-full px-6 py-3 transition-all shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                    fill="#4285F4" />
                                <path
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                    fill="#34A853" />
                                <path
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                    fill="#FBBC05" />
                                <path
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                    fill="#EA4335" />
                            </svg>
                            <span>Sign up with Google</span>
                        </a>
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-gray-800">Already have an account?</span>
                            <a class="text-center border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded-xl hover:bg-gray-50 transition-colors"
                                href="{{ route('login') }}">Login</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
