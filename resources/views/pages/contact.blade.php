@extends('layouts.app')


@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-10 py-10">
                <h2 class="text-4xl font-bold text-gray-800 mb-6 text-center">Contact Us</h2>
                <p class="text-gray-600 mb-8 text-center">
                    Have questions or feedback? We'd love to hear from you. Fill out the form below and we'll get back to you as soon as possible.
                </p>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <form id="contact-form" method="POST" action="{{ route('contact.submit') }}">
                    @csrf
                    <fieldset>
                        <legend class="sr-only">Contact Form</legend>

                        <x-ui.input 
                        label="Name" 
                        name="name" 
                        type="text" 
                        value="{{ old('name') }}" 
                        :error="$errors->first('name')"
                        required 
                        class="text-xl mb-4" 
                    />

                    <x-ui.input 
                        label="Email (Optional)" 
                        name="email" 
                        type="email" 
                        value="{{ old('email') }}" 
                        :error="$errors->first('email')"
                        class="text-xl mb-4" 
                        help="We will use this email to reply to your inquiry."
                    />

                    <x-ui.input 
                        label="Message" 
                        name="message" 
                        type="textarea" 
                        value="{{ old('message') }}" 
                        :error="$errors->first('message')"
                        required 
                        rows="6"
                        class="text-xl mb-6" 
                    />

                    <div class="flex justify-center">
                        <x-ui.button id="submit-button" type="submit" variant="primary" class="w-full md:w-auto px-8 py-3 text-lg">
                            Send Message
                        </x-ui.button>
                    </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            const button = document.getElementById('submit-button');
            
            // Disable button and show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
            button.classList.add('opacity-75', 'cursor-not-allowed');
        });
    </script>
@endsection
