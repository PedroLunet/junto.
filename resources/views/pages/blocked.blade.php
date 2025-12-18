@extends('layouts.app')

@section('content')
    <!-- Blocked Modal Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 md:p-12 text-center">
                <!-- Icon -->
                <div class="mb-6 sm:mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 sm:w-32 sm:h-32 bg-red-100 rounded-full">
                        <i class="fas fa-ban text-4xl sm:text-6xl text-red-600"></i>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-3xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">
                    Account Blocked
                </h1>

                <!-- Message -->
                <p class="text-lg sm:text-xl md:text-2xl text-gray-600 mb-6 sm:mb-8">
                    Your account has been blocked.
                </p>

                <p class="text-base sm:text-base md:text-lg text-gray-500 mb-8 sm:mb-12">
                    If you believe this is a mistake, please appeal for unblock.
                </p>


                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <x-ui.button variant="secondary" onclick="appealUnblock()"
                        class="text-lg sm:text-xl md:text-xl px-8 sm:px-10 md:px-12 py-3 sm:py-4 w-full sm:w-auto">
                        <i class="fas fa-envelope mr-2 sm:mr-3"></i>
                        Appeal for Unblock
                    </x-ui.button>
                    <x-ui.button variant="primary" onclick="window.location.href='{{ route('logout') }}'"
                        class="text-lg sm:text-xl md:text-xl px-8 sm:px-10 md:px-12 py-3 sm:py-4 w-full sm:w-auto">
                        <i class="fas fa-sign-out-alt mr-2 sm:mr-3"></i>
                        Logout
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function appealUnblock() {
            const confirmed = await alertConfirm(
                'This will send an appeal request to the administrators. Do you want to proceed?',
                'Appeal for Unblock'
            );

            if (confirmed) {
                // TODO: appeal functionality
                await alertInfo('Your appeal has been submitted successfully. Administrators will review it shortly.');
            }
        }
    </script>
@endsection
