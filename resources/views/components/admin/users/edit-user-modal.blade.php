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

        function closeModalHandler() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // reset form
            form.reset();
            // clear error messages
            const errorElements = form.querySelectorAll('.error-message');
            errorElements.forEach(el => el.remove());
            // reset field styling
            const errorFields = form.querySelectorAll('.border-red-500');
            errorFields.forEach(field => field.classList.remove('border-red-500'));
        }

        // make closeModalHandler available globally
        window.closeEditUserModal = closeModalHandler;

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
        window.openEditUserModal = function(userData) {
            // populate form with user data
            document.getElementById('editUserId').value = userData.id;
            document.getElementById('editName').value = userData.name;
            document.getElementById('editUsername').value = userData.username;
            document.getElementById('editBio').value = userData.bio || '';
            document.getElementById('editIsAdmin').checked = userData.isadmin;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // focus the name input after a short delay
            setTimeout(() => {
                document.getElementById('editName').focus();
            }, 100);
        };

        // form submission handler
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = document.querySelector('button[type="submit"][form="editUserForm"]');
            const originalText = submitButton.textContent;
            const userId = document.getElementById('editUserId').value;

            // disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Updating...';

            // clear previous error messages
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

            console.log('Updating user:', userId, 'with data:', data);

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
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        // close modal and reload page
                        closeModalHandler();
                        window.location.reload();
                    } else {
                        // show error message
                        if (data.errors) {
                            // handle validation errors
                            Object.keys(data.errors).forEach(field => {
                                showFieldError(field, data.errors[field][0]);
                            });
                        } else {
                            showError('An error occurred: ' + (data.message || 'Unknown error'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while updating the user');
                })
                .finally(() => {
                    // re-enable submit button
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
        });

        // helper function to show general error messages
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className =
                'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>' + message + '</span>';

            const firstInput = form.querySelector('input');
            firstInput.parentNode.insertBefore(errorDiv, firstInput);
        }

        // helper function to show field-specific error messages
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
