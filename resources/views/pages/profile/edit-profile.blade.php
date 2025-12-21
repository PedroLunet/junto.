@extends('layouts.app')

@section('content')
    <!-- Alert Container -->
    <div id="alertContainer" class="fixed top-20 right-8 z-50 min-w-[500px]"></div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-10 gap-6">
            <a href="{{ route('profile.show', $user->username) }}" class="mr-4 text-gray-600 hover:text-gray-800 p-2">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
            </div>
        </div>

        <x-ui.tabs :tabs="[
            'details' => [
                'title' => 'Details',
                'content' => view('components.profile.edit-details-tab', ['user' => $user])->render(),
            ],
            'security' => [
                'title' => 'Security',
                'content' => view('components.profile.edit-security-tab', ['user' => $user])->render(),
            ],
        ]" />
    </div>

    <!-- Change Password Modal -->
    <x-profile.change-password-modal />
@endsection

@push('scripts')
    <script>
        // Function to show alert
        window.showAlert = function(type, title, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();

            // Create a temporary form to render the Blade component via AJAX
            fetch('{{ route('profile.render-alert') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'text/html'
                    },
                    body: JSON.stringify({
                        type: type,
                        title: title,
                        message: message,
                        id: alertId
                    })
                })
                .then(response => response.text())
                .then(html => {
                    alertContainer.insertAdjacentHTML('beforeend', html);

                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        const alert = document.getElementById(alertId);
                        if (alert) {
                            alert.style.display = 'none';
                        }
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error showing alert:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const privacyToggle = document.querySelector('#privacy-toggle');

            if (privacyToggle) {
                privacyToggle.addEventListener('change', function() {
                    fetch('{{ route('profile.toggle-privacy') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Toggle was successful
                                console.log('Privacy setting updated:', data.isprivate ? 'Private' :
                                    'Public');
                            } else {
                                // Revert toggle on error
                                privacyToggle.checked = !privacyToggle.checked;
                                alert(data.message || 'Failed to update privacy setting');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Revert toggle on error
                            privacyToggle.checked = !privacyToggle.checked;
                            alert('An error occurred while updating your privacy setting');
                        });
                });
            }
        });
    </script>
@endpush
