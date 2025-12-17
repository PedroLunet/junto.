@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-20 gap-10">
            <div class="flex items-center gap-10">
                <a href="{{ route('profile.show', $user->username) }}" class="mr-4 text-gray-600 hover:text-gray-800 p-3">
                    <i class="fas fa-arrow-left text-3xl"></i>
                </a>
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">Edit Profile</h1>
                </div>
            </div>
            <x-ui.button type="submit" variant="primary" class="text-3xl mr-20" id="saveProfileBtn" form="editProfileForm">
                Save Changes
            </x-ui.button>
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
