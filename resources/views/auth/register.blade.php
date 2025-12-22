@extends('pages.home')

@section('modal-overlay')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative overflow-hidden">
            <div class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-800 m-0">Create Account</h2>
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
                    <fieldset>
                        <legend class="sr-only">Registration Form</legend>

                        <x-ui.input label="Name" name="name" type="text" value="{{ old('name') }}" :error="$errors->first('name')"
                            required autofocus />

                        <x-ui.input label="Username" name="username" type="text" value="{{ old('username') }}"
                            :error="$errors->first('username')" required minlength="4" pattern="[a-zA-Z0-9._-]+"
                            help="Username can only contain letters, numbers, dots (.), dashes (-) and underscores (_), and must be at least 4 characters long." />

                        <x-ui.input label="E-mail Address" name="email" type="email" value="{{ old('email') }}"
                            :error="$errors->first('email')" required />

                        <x-ui.input label="Password" name="password" type="password" :error="$errors->first('password')" required 
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}"
                            title="Must contain at least one number, one uppercase and lowercase letter, one special character, and at least 8 or more characters" />

                        <div id="password_requirements" class="mb-6 space-y-1 text-sm hidden transition-all duration-300 ease-in-out">
                            <p class="text-gray-600 font-medium mb-2 text-xs">
                                Password requirements:
                            </p>
                            <p id="req_length" class="text-gray-500 text-xs transition-colors duration-200" data-text="Minimum characters: 8">
                                <span class="mr-2">•</span>Minimum characters: 8
                            </p>
                            <p id="req_uppercase" class="text-gray-500 text-xs transition-colors duration-200" data-text="One uppercase character">
                                <span class="mr-2">•</span>One uppercase character
                            </p>
                            <p id="req_lowercase" class="text-gray-500 text-xs transition-colors duration-200" data-text="One lowercase character">
                                <span class="mr-2">•</span>One lowercase character
                            </p>
                            <p id="req_special" class="text-gray-500 text-xs transition-colors duration-200" data-text="One special character">
                                <span class="mr-2">•</span>One special character
                            </p>
                            <p id="req_number" class="text-gray-500 text-xs transition-colors duration-200" data-text="One number">
                                <span class="mr-2">•</span>One number
                            </p>
                        </div>

                        <x-ui.input label="Confirm Password" name="password_confirmation" type="password" required />

                        <p class="text-sm text-gray-600 mb-4 w-full text-center"><span class="text-red-500">*</span> Mandatory fields</p>

                        <div class="flex flex-col gap-4 items-center">
                            <x-ui.button type="submit" variant="primary" class="w-full py-3 rounded-full shadow-sm hover:shadow-md">Register</x-ui.button>
                            <a href="{{ route('google-auth') }}"
                                class="w-full flex items-center justify-center gap-3 bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-normal rounded-full px-6 py-3 transition-all shadow-sm hover:shadow-md">
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
                                <a class="text-center text-gray-700 font-bold py-2"
                                    href="{{ route('login') }}">Login</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            const requirementsDiv = document.getElementById('password_requirements');

            if (passwordInput) {
                // Show requirements on focus
                passwordInput.addEventListener('focus', function() {
                    requirementsDiv.classList.remove('hidden');
                });

                // Validate on input
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    
                    const requirements = {
                        length: password.length >= 8,
                        uppercase: /[A-Z]/.test(password),
                        lowercase: /[a-z]/.test(password),
                        special: /[^a-zA-Z0-9]/.test(password),
                        number: /[0-9]/.test(password)
                    };

                    // update UI for each requirement
                    Object.keys(requirements).forEach(req => {
                        const elem = document.getElementById('req_' + req);
                        const text = elem.getAttribute('data-text');
                        elem.classList.remove('text-gray-500', 'text-green-600', 'text-red-600');
                        
                        if (password.length > 0) {
                            if (requirements[req]) {
                                elem.classList.add('text-green-600');
                                elem.innerHTML = '<i class="fas fa-check mr-2"></i>' + text;
                            } else {
                                elem.classList.add('text-red-600');
                                elem.innerHTML = '<span class="mr-2">•</span>' + text;
                            }
                        } else {
                            elem.classList.add('text-gray-500');
                            elem.innerHTML = '<span class="mr-2">•</span>' + text;
                        }
                    });
                });
            }
        });
    </script>
@endpush
