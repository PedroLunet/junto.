<div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 class="text-4xl font-bold text-gray-900">Edit Profile</h2>
            <x-ui.button id="closeEditProfileModal" variant="ghost">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </x-ui.button>
        </div>

        <!-- body -->
        <div class="flex-1 px-8">
            <form id="editProfileForm" class="space-y-6">
                @csrf

                <div class="flex items-center gap-6">
                    <!-- profile picture -->
                    <div class="shrink-0 w-40 h-40 rounded-full overflow-hidden relative">
                        <img src="{{ $user->profile_picture ?? asset('profile/image.png') }}" alt="Profile Picture"
                            class="absolute inset-0 w-full h-full object-cover">
                    </div>

                    <div class="flex-1 space-y-6">
                        <!-- name -->
                        <div>
                            <label for="editName" class="block text-2xl font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" id="editName" name="name" value="{{ $user->name ?? '' }}"
                                class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                        </div>

                        <!-- username -->
                        <div>
                            <label for="editUsername"
                                class="block text-2xl font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" id="editUsername" name="username" value="{{ $user->username ?? '' }}"
                                class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- bio -->
                <div>
                    <label for="editBio" class="block text-2xl font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="editBio" name="bio" rows="4" placeholder="Tell others about yourself..."
                        class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent resize-none">{{ $user->bio ?? '' }}</textarea>
                </div>
            </form>
        </div>

        <!-- footer -->
        <div class="flex justify-end gap-4 p-8">
            <x-ui.button type="button" onclick="closeEditProfileModal()" variant="secondary"
                class="text-2xl font-medium">
                Cancel
                </x-button>
                <x-ui.button type="submit" form="editProfileForm" variant="primary" class="text-2xl font-medium">
                    Save Changes
                    </x-button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editProfileModal');
        const closeModal = document.getElementById('closeEditProfileModal');
        const form = document.getElementById('editProfileForm');

        function closeModalHandler() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // make closeModalHandler available globally
        window.closeEditProfileModal = closeModalHandler;

        // close modal event listeners
        closeModal.addEventListener('click', closeModalHandler);

        // close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModalHandler();
            }
        });

        // close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModalHandler();
            }
        });

        // open modal function
        window.openEditProfileModal = function() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // focus the name input after a short delay
            setTimeout(() => {
                document.getElementById('editName').focus();
            }, 100);
        };

        // form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = document.querySelector(
                'button[type="submit"][form="editProfileForm"]');
            const originalText = submitButton.textContent;

            // disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';

            // clear previous error messages
            const errorElements = form.querySelectorAll('.error-message');
            errorElements.forEach(el => el.remove());

            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                username: formData.get('username'),
                bio: formData.get('bio')
            };

            fetch('/profile/update', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // close modal and redirect if username changed
                        closeModalHandler();
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        // show error message
                        showError('An error occurred: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while updating your profile');
                })
                .finally(() => {
                    // re-enable submit button
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
        });

        // helper function to show error messages
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className =
                'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
            errorDiv.textContent = message;

            const firstInput = form.querySelector('input');
            firstInput.parentNode.insertBefore(errorDiv, firstInput);
        }

    });
</script>
