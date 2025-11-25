@extends('pages.home')

@section('modal-overlay')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative overflow-hidden">
            <div class="p-10">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-4xl font-bold text-gray-800 m-0">Create Account</h2>
                    <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block font-medium text-gray-700 mb-1">Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800"
                        >
                        @error('name')
                            <span id="name-error" class="text-red-500 text-sm mt-1 block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block font-medium text-gray-700 mb-1">E-mail Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            inputmode="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800"
                        >
                        @error('email')
                            <span id="email-error" class="text-red-500 text-sm mt-1 block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block font-medium text-gray-700 mb-1">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800"
                        >
                        @error('password')
                            <span id="password-error" class="text-red-500 text-sm mt-1 block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password-confirm" class="block font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input
                            id="password-confirm"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-violet-800"
                        >
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="w-full bg-[#38157a] text-white font-bold py-2 px-4 rounded hover:bg-[#7455ad] transition-colors">Register</button>
                        <a class="w-full text-center border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded hover:bg-gray-50 transition-colors" href="{{ route('login') }}">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
