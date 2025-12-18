@extends('layouts.app')

@section('content')
    <!-- Blocked Modal Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center">
        <div class="max-w-2xl w-full mx-4">
            <div class="bg-white rounded-3xl shadow-2xl p-12 text-center">
                <!-- Icon -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-32 h-32 bg-red-100 rounded-full">
                        <i class="fas fa-ban text-6xl text-red-600"></i>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-5xl font-bold text-gray-900 mb-6">
                    Account Blocked
                </h1>

                <!-- Message -->
                <p class="text-2xl text-gray-600 mb-8">
                    Your account has been blocked.
                </p>

                <p class="text-xl text-gray-500 mb-12">
                    If you believe this is a mistake, please appeal for unblock.
                </p>

                
                <div class="flex justify-center gap-4">
                    <x-ui.button variant="secondary" onclick="appealUnblock()" class="text-2xl px-12 py-4">
                        <i class="fas fa-envelope mr-3"></i>
                        Appeal for Unblock
                    </x-ui.button>
                    <x-ui.button variant="primary" onclick="window.location.href='{{ route('logout') }}'"
                        class="text-2xl px-12 py-4">
                        <i class="fas fa-sign-out-alt mr-3"></i>
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
