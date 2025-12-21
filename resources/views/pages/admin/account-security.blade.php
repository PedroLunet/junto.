@extends('layouts.admin')

@section('page-title', 'Account Security')

@section('content')
    <!-- Alert Container -->
    <div id="alertContainer" class="fixed top-20 right-8 z-50 min-w-[500px]"></div>

    <div class="flex flex-col h-[calc(100vh-4rem)]">
        <!-- Fixed Header -->
        <div class="flex-none bg-[#F1EBF4]">
            <div
                class="mx-4 sm:mx-8 lg:mx-20 mt-6 sm:mt-8 lg:mt-10 mb-4 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 lg:gap-0">
                <div class="w-full lg:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Account Security</h1>
                    <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Manage your admin account details and security
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
        // Function to show alert
        window.showAlert = function(type, title, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();

            // Create alert element
            const alertHtml = `
                <div id="${alertId}" class="bg-white border-l-4 ${type === 'success' ? 'border-green-500' : 'border-red-500'} rounded-lg shadow-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas ${type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500'} text-2xl mr-3"></i>
                            <div>
                                <p class="font-semibold text-gray-800">${title}</p>
                                <p class="text-sm text-gray-600">${message}</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('${alertId}').remove()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            alertContainer.insertAdjacentHTML('beforeend', alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

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
