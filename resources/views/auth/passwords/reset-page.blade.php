@extends('layouts.minimal')

@section('title', 'Reset Password')

@section('content')
<div class="max-w-3xl mx-auto mt-8">
    <div class="bg-white p-8 rounded-2xl shadow-sm">
        <h2 class="text-3xl font-bold mb-2 text-[#624452]">Reset Password</h2>
        <p class="text-gray-600 mb-8 text-xl">Please enter your new password below.</p>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <x-ui.input 
                label="Email Address" 
                name="email" 
                type="email" 
                value="{{ $email ?? old('email') }}" 
                :error="$errors->first('email')"
                required 
                readonly
                class="bg-gray-100 cursor-not-allowed"
            />

            <x-ui.input 
                label="New Password" 
                name="password" 
                type="password" 
                :error="$errors->first('password')"
                required 
                autocomplete="new-password"
            />

            <x-ui.input 
                label="Confirm Password" 
                name="password_confirmation" 
                type="password" 
                required 
                autocomplete="new-password"
            />

            <div class="pt-4">
                <x-ui.button type="submit" variant="primary" class="w-full">
                    Reset Password
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
