<div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-sm sm:max-w-md md:max-w-lg lg:max-w-lg xl:max-w-xl mx-2 sm:mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-2 sm:p-4 md:p-8 lg:p-10">
            <h2 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold text-gray-900">Edit User</h2>
            <x-ui.icon-button id="closeEditUserModal" onclick="closeEditUserModal()" variant="gray" aria-label="Close">
                <i class="fas fa-times text-base sm:text-xl md:text-2xl"></i>
            </x-ui.icon-button>
        </div>

        <!-- body -->
        <div class="flex-1 px-2 sm:px-4 md:px-6 lg:px-10">
            <form id="editUserForm" class="space-y-4 sm:space-y-6 md:space-y-8">
                @csrf
                <input type="hidden" id="editUserId" name="id">

                <x-ui.input label="Name" type="text" name="name" id="editName" placeholder="Enter user's name"
                    required />

                <x-ui.input label="Username" type="text" name="username" id="editUsername"
                    placeholder="Enter username" required />

                <x-ui.input label="Bio (Optional)" type="textarea" name="bio" id="editBio"
                    placeholder="Tell others about this user..." :rows="3" />

                <!-- admin status -->
                <div class="mb-2 sm:mb-4">
                    <label class="flex items-center space-x-2 sm:space-x-4">
                        <input type="checkbox" id="editIsAdmin" name="is_admin"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded border-gray-300 text-[#38157a] focus:ring-[#38157a] shrink-0">
                        <span class="text-base sm:text-xl md:text-2xl font-medium text-gray-700">Admin User</span>
                    </label>
                </div>
            </form>
        </div>

        <!-- footer -->
        <div class="flex justify-end gap-2 sm:gap-4 p-2 sm:p-4 md:p-8 mt-2 sm:mt-4 md:mt-8">
            <x-ui.button variant="secondary" type="button" onclick="closeEditUserModal()"
                class="text-base sm:text-xl md:text-2xl">
                Cancel
            </x-ui.button>
            <x-ui.button variant="primary" type="submit" form="editUserForm"
                class="text-base sm:text-xl md:text-2xl px-4 sm:px-8 md:px-10 py-2 sm:py-3 md:py-4">
                Update User
            </x-ui.button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editUserModal');
        const closeModal = document.getElementById('closeEditUserModal');
        const form = document.getElementById('editUserForm');

        /**
         * Closes the modal and resets the form state
         */
        function closeModalHandler() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Reset form
            form.reset();

            // Clear error messages
            const errorElements = form.querySelectorAll('.error-message');
            errorElements.forEach(el => el.remove());

            // Reset field styling
            const errorFields = form.querySelectorAll('.border-red-500');
            errorFields.forEach(field => field.classList.remove('border-red-500'));
        }

        // Make closeModalHandler available globally
        window.closeEditUserModal = closeModalHandler;

        // Close modal event listeners
        closeModal.addEventListener('click', closeModalHandler);

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModalHandler();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModalHandler();
            }
        });

        /**
         * Opens the modal and populates it with user data
         */
        window.openEditUserModal = function(userData) {
            // Populate form with user data
            document.getElementById('editUserId').value = userData.id;
            document.getElementById('editName').value = userData.name;
            document.getElementById('editUsername').value = userData.username;
            document.getElementById('editBio').value = userData.bio || '';
            document.getElementById('editIsAdmin').checked = userData.isadmin;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focus the name input after a short delay
            setTimeout(() => {
                document.getElementById('editName').focus();
            }, 100);
        };

        // Form submission handler
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = document.querySelector('button[type="submit"][form="editUserForm"]');
            const originalText = submitButton.textContent;
            const userId = document.getElementById('editUserId').value;

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Updating...';

            // Clear previous error messages
            const errorElements = form.querySelectorAll('.error-message');
            errorElements.forEach(el => el.remove());
            const errorFields = form.querySelectorAll('.border-red-500');
            errorFields.forEach(field => field.classList.remove('border-red-500'));

            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                username: formData.get('username'),
                bio: formData.get('bio'),
                is_admin: formData.get('is_admin') ? true : false
            };

            fetch(`/admin/users/${userId}`, {
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
                        closeModalHandler();
                        showAlertCard('success', 'Success', 'User updated successfully.');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                showFieldError(field, data.errors[field][0]);
                            });
                        } else {
                            showAlertCard('error', 'Error', 'An error occurred: ' + (data.message ||
                                'Unknown error'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while updating the user');
                })
                .finally(() => {
                    // Re-enable submit button
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
        });

        /**
         * Helper: Show general error messages
         */
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className =
                'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> <span>${message}</span>`;
            form.prepend(errorDiv); // Added line to actually show the error
        }

        /**
         * Helper: Show dynamic alert cards (Success/Error)
         */
        function showAlertCard(type, title, message) {
            // Remove any existing alert card
            const existing = document.getElementById('js-dynamic-alert-card');
            if (existing) existing.remove();

            // Create wrapper div
            const wrapper = document.createElement('div');
            wrapper.id = 'js-dynamic-alert-card';

            const isSuccess = type === 'success';
            const bgColor = isSuccess ? 'bg-green-50' : 'bg-red-50';
            const borderColor = isSuccess ? 'border-green-200' : 'border-red-200';
            const iconBg = isSuccess ? 'bg-green-200' : 'bg-red-200';
            const textColor = isSuccess ? 'text-green-600' : 'text-red-600';

            wrapper.innerHTML = `
            <div class='fixed top-6 right-6 z-50 flex items-start gap-3 px-4 py-4 rounded-2xl border ${bgColor} ${borderColor} shadow-sm mb-4 min-w-[280px] max-w-xs transition-all duration-300 ease-in-out opacity-0 translate-x-full'>
                <div class='shrink-0 flex items-center justify-center w-8 h-8 rounded-full ${iconBg}'>
                    ${isSuccess
                        ? `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke-width='2' class='stroke-green-400 fill-green-50'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4' class='stroke-green-600'/></svg>`
                        : `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke-width='2' class='stroke-red-400 fill-red-50'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 9l-6 6m0-6l6 6' class='stroke-red-600'/></svg>`}
                </div>
                <div class='flex-1'>
                    <div class='font-semibold text-base ${textColor} mb-0.5'>${title}</div>
                    <div class='text-gray-700 text-sm'>${message}</div>
                </div>
                <button type='button' class='absolute top-2 right-2' onclick='this.closest("#js-dynamic-alert-card").remove()'>
                    <i class='fa fa-times w-5 h-5'></i>
                </button>
            </div>
        `;
            document.body.appendChild(wrapper);

            // Animate in
            setTimeout(() => {
                const alert = wrapper.firstElementChild;
                if (alert) {
                    alert.classList.remove('opacity-0', 'translate-x-full');
                    alert.classList.add('opacity-100', 'translate-x-0');
                }
            }, 10);

            // Animate out after 5s
            setTimeout(() => {
                const alert = wrapper.firstElementChild;
                if (!alert) return;
                alert.classList.remove('opacity-100', 'translate-x-0');
                alert.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => {
                    wrapper.remove();
                }, 300);
            }, 5000);
        }

        /**
         * Helper: Show field-specific validation errors
         */
        function showFieldError(fieldName, message) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-600 text-sm mt-1';
                errorDiv.textContent = message;

                field.parentNode.appendChild(errorDiv);
                field.classList.add('border-red-500');
            }
        }
    });
</script>
