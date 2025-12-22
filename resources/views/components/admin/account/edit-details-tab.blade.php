<form id="editAdminForm" class="space-y-6" method="POST" action="{{ route('admin.account-security.update') }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        <!-- name -->
        <x-ui.input label="Name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}"
            :error="$errors->first('name')" />

        <!-- username -->
        <x-ui.input label="Username" name="username" type="text" value="{{ old('username', $user->username ?? '') }}"
            :error="$errors->first('username')" />

        <!-- email -->
        <x-ui.input label="Email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}"
            :error="$errors->first('email')" />
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
        <x-ui.button type="submit" variant="primary" class="text-xl" id="saveAdminBtn">
            Save Changes
        </x-ui.button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editAdminForm');
        const saveBtn = document.getElementById('saveAdminBtn');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (typeof showAlert === 'function') {
                            showAlert('success', 'Success', data.message);
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                })
                .catch((error) => {
                    // Clear previous errors
                    document.querySelectorAll('.input-error-message').forEach(el => el.remove());
                    document.querySelectorAll('.border-red-500').forEach(el => {
                        el.classList.remove('border-red-500', 'focus:border-red-500',
                            'focus:ring-red-100');
                        el.classList.add('border-gray-300');
                    });
                    document.querySelectorAll('.input-error-icon').forEach(el => el.remove());

                    // Handle validation errors
                    if (error.errors) {
                        Object.keys(error.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                // Add error border
                                input.classList.remove('border-gray-300');
                                input.classList.add('border-red-500',
                                    'focus:border-red-500', 'focus:ring-red-100',
                                    'pr-12', '!border-red-500');

                                // Add error icon
                                const iconHtml =
                                    `<span class="absolute right-4 flex items-center justify-center pointer-events-none input-error-icon"><i class="fas fa-exclamation-circle text-red-500 text-3xl"></i></span>`;
                                input.parentElement.insertAdjacentHTML('beforeend',
                                    iconHtml);

                                // Add error message
                                const errorMsg =
                                    `<p class="mt-2 text-xl text-red-500 font-medium input-error-message">${error.errors[field][0]}</p>`;
                                input.parentElement.parentElement.insertAdjacentHTML(
                                    'beforeend', errorMsg);

                                // Add input event listener to clear error on typing
                                input.addEventListener('input', function clearError() {
                                    input.classList.remove('border-red-500',
                                        'focus:border-red-500',
                                        'focus:ring-red-100', 'pr-12',
                                        '!border-red-500');
                                    input.classList.add('border-gray-300');

                                    const errorIcon = input.parentElement
                                        .querySelector('.input-error-icon');
                                    if (errorIcon) errorIcon.remove();

                                    const errorMessage = input.parentElement
                                        .parentElement.querySelector(
                                            '.input-error-message');
                                    if (errorMessage) errorMessage.remove();

                                    input.removeEventListener('input', clearError);
                                }, {
                                    once: false
                                });
                            }
                        });
                        if (typeof showAlert === 'function') {
                            showAlert('error', 'Error', 'Please fix the errors in the form');
                        }
                    } else {
                        if (typeof showAlert === 'function') {
                            showAlert('error', 'Error', error.message || 'An error occurred');
                        }
                    }
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                });
        });
    });
</script>
