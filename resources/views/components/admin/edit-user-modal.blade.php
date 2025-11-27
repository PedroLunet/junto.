<div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 class="text-3xl font-bold text-gray-900">Edit User</h2>
            <a id="closeEditUserModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </a>
        </div>

        <!-- body -->
        <div class="flex-1 px-8">
            <form id="editUserForm" class="space-y-6">
                @csrf
                <input type="hidden" id="editUserId" name="user_id">

                <!-- name -->
                <div>
                    <label for="editName" class="block text-lg font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" id="editName" name="name" required
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                </div>

                <!-- username -->
                <div>
                    <label for="editUsername" class="block text-lg font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" id="editUsername" name="username" required
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                </div>

                <!-- email -->
                <div>
                    <label for="editEmail" class="block text-lg font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="editEmail" name="email" required
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                </div>

                <!-- bio -->
                <div>
                    <label for="editBio" class="block text-lg font-medium text-gray-700 mb-2">Bio (Optional)</label>
                    <textarea id="editBio" name="bio" rows="3" placeholder="Tell others about this user..."
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent resize-none"></textarea>
                </div>

                <!-- admin status -->
                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" id="editIsAdmin" name="is_admin"
                            class="rounded border-gray-300 text-[#38157a] focus:ring-[#38157a]">
                        <span class="text-lg font-medium text-gray-700">Admin User</span>
                    </label>
                </div>

            </form>
        </div>

        <!-- footer -->
        <div class="flex justify-end gap-4 p-8">
            <x-button variant="secondary" type="button" onclick="closeEditUserModal()" class="text-lg">
                Cancel
            </x-button>
            <x-button variant="primary" type="submit" form="editUserForm" class="text-lg">
                Update User
            </x-button>
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
            document.getElementById('editEmail').value = userData.email;
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
                email: formData.get('email'),
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
                'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
            errorDiv.textContent = message;

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
