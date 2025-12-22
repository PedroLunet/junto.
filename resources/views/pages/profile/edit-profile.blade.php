@extends('layouts.app')

@section('content')
    @if (session('success'))
        <x-ui.alert-card type="success" title="Success" :message="session('success')" dismissible="true" class="mb-6" />
    @endif

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
                .then(response => {
                    console.log('showAlert fetch response status:', response.status);
                    return response.text();
                })
                .then(html => {
                    console.log('showAlert received HTML:', html);
                    alertContainer.insertAdjacentHTML('beforeend', html);

                    // Execute any scripts in the inserted HTML (needed for alert animation)
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    tempDiv.querySelectorAll('script').forEach(script => {
                        try {
                            eval(script.innerText);
                        } catch (e) {
                            console.error('Error executing alert script:', e);
                        }
                    });

                    // Auto-dismiss after 5 seconds (fallback)
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
            // Show alert if redirected after password or privacy change
            const params = new URLSearchParams(window.location.search);
            if (params.get('password_changed') === '1') {
                if (window.showAlert && typeof window.showAlert === 'function') {
                    window.showAlert('success', 'Success', 'Password changed successfully!');
                } else {
                    alert('Password changed successfully!');
                }
                // Remove the param from the URL without reloading
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('password_changed');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                }
            } else if (params.get('privacy_changed') === '1') {
                if (window.showAlert && typeof window.showAlert === 'function') {
                    window.showAlert('success', 'Success', 'Privacy setting updated successfully!');
                } else {
                    alert('Privacy setting updated successfully!');
                }
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('privacy_changed');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                }
            }
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
                            if (data.success && data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else if (!data.success) {
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
