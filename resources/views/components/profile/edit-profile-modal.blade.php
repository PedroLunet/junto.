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
                    <div class="shrink-0 w-40 h-40 relative">
                        <div class="w-full h-full rounded-full overflow-hidden relative">
                            <img id="profileImagePreview"
                                src="{{ $user->profilepicture ? asset('profile/' . $user->profilepicture) : asset('profile/default.png') }}"
                                alt="Profile Picture" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                        <x-ui.button variant="outline"
                            class="absolute -top-2 -right-2 w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                            <i class="fas fa-edit text-purple-500"></i>
                        </x-ui.button>
                        <x-ui.button variant="outline"
                            class="absolute -top-2 -left-2 w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                            <i class="fas fa-trash text-red-500"></i>
                        </x-ui.button>
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

                <!-- password (fake input, styled like others) -->
                <div>
                    <label class="block text-2xl font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-[#38157a] focus:border-transparent flex items-center select-none pr-14"
                            style="letter-spacing: 0.3em;">
                            <span aria-label="Password hidden" class="tracking-widest text-gray-500 flex-1">
                                <span
                                    class="inline-block mx-0.5 text-3xl align-middle">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                            </span>
                            <x-ui.button variant="outline"
                                class="absolute right-2 top-0 bottom-0 my-auto h-12 w-12 p-2 flex items-center justify-center text-2xl font-bold px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100"
                                title="Edit Password">
                                <i
                                    class="fas fa-edit text-purple-500 leading-none align-middle flex items-center justify-center"></i>
                            </x-ui.button>
                        </div>
                    </div>
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
