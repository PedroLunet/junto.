@extends('layouts.admin')

@section('page-title', 'Account Security')

@section('content')
    @if (session('success'))
        <x-ui.alert-card type="success" title="Success" :message="session('success')" dismissible="true" class="mb-6" />
    @endif
    @if (session('error'))
        <x-ui.alert-card type="error" title="Error" :message="session('error')" dismissible="true" class="mb-6" />
    @endif

    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div
                class="mx-4 sm:mx-8 lg:mx-20 mt-6 sm:mt-8 lg:mt-10 mb-4 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 lg:gap-0">
                <div class="w-full lg:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Account Security</h1>
                    <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Manage your admin account details and
                        security
                        settings</p>
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="my-4 sm:my-6">

                <x-ui.tabs :tabs="[
                    'details' => [
                        'title' => 'Details',
                        'content' => view('components.admin.account.edit-details-tab', ['user' => $user])->render(),
                    ],
                    'security' => [
                        'title' => 'Security',
                        'content' => view('components.admin.account.edit-security-tab', ['user' => $user])->render(),
                    ],
                ]" />
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <x-admin.account.change-password-modal />
@endsection

@push('scripts')
    <script>
        // Password modal functions
        window.openPasswordModal = function() {
            document.getElementById('changePasswordModal').classList.remove('hidden');
        }

        window.closePasswordModal = function() {
            document.getElementById('changePasswordModal').classList.add('hidden');
            document.getElementById('changePasswordForm').reset();
        }
    </script>
@endpush
