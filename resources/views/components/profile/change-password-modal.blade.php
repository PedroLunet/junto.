<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 z-50 hidden" onclick="closePasswordModal()">
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <!-- Modal panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl"
                onclick="event.stopPropagation()">

                <div class="p-12">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-semibold text-gray-900">Change Password</h2>
                        <x-ui.button type="button" variant="ghost" onclick="closePasswordModal()" class="p-2">
                            <i class="fas fa-times text-3xl"></i>
                        </x-ui.button>
                    </div>

                    <form id="changePasswordForm" class="space-y-6">
                        <!-- Old Password -->
                        <div>
                            <x-ui.input label="Old Password" type="password" name="old_password" id="old_password"
                                placeholder="Enter your old password" required />
                            <p id="old_password_check" class="mt-2 text-xl"></p>
                        </div>

                        <!-- New Password -->
                        <div>
                            <x-ui.input label="New Password" type="password" name="new_password" id="new_password"
                                placeholder="Enter your new password" required />
                            <div id="password_requirements" class="mt-3 space-y-1 text-sm">
                                <p class="text-gray-600 font-medium mb-2 text-sm">
                                    Please add all necessary characters to create safe password.
                                </p>
                                <p id="req_length" class="text-gray-500 text-sm">
                                    <span class="mr-2">•</span>Minimum characters: 12
                                </p>
                                <p id="req_uppercase" class="text-gray-500 text-sm">
                                    <span class="mr-2">•</span>One uppercase character
                                </p>
                                <p id="req_lowercase" class="text-gray-500 text-sm">
                                    <span class="mr-2">•</span>One lowercase character
                                </p>
                                <p id="req_special" class="text-gray-500 text-sm">
                                    <span class="mr-2">•</span>One special character
                                </p>
                                <p id="req_number" class="text-gray-500 text-sm">
                                    <span class="mr-2">•</span>One number
                                </p>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <x-ui.input label="Confirm New Password" type="password" name="confirm_password"
                                id="confirm_password" placeholder="Re-enter your new password" required />
                            <p id="confirm_password_match" class="mt-2 text-xl"></p>
                        </div>

                        <!-- Error message -->
                        <div id="password_error" class="hidden">
                            <p class="text-red-600 text-xl"></p>
                        </div>

                        <!-- Change Password Button -->
                        <x-ui.button type="submit" variant="primary" class="w-full text-base">
                            Change Password
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // open password modal
    window.openPasswordModal = function() {
        document.getElementById('changePasswordModal').classList.remove('hidden');
        document.getElementById('changePasswordForm').reset();
        clearPasswordValidation();
    };

    // close password modal
    window.closePasswordModal = function() {
        document.getElementById('changePasswordModal').classList.add('hidden');
        document.getElementById('changePasswordForm').reset();
        clearPasswordValidation();
    };

    // clear validation messages
    function clearPasswordValidation() {
        document.getElementById('old_password_check').textContent = '';
        document.getElementById('confirm_password_match').textContent = '';
        document.getElementById('password_error').classList.add('hidden');

        // reset old password styling
        const oldPasswordInput = document.querySelector('input[name="old_password"]');
        if (oldPasswordInput) {
            oldPasswordInput.classList.remove('!border-green-500', 'focus:!border-green-500', 'focus:!ring-green-100',
                '!border-red-500', 'focus:!border-red-500', 'focus:!ring-red-100');
        }
        const oldPasswordIcon = document.getElementById('old_password_icon');
        if (oldPasswordIcon) {
            oldPasswordIcon.remove();
        }

        // reset requirement styles
        ['length', 'uppercase', 'lowercase', 'special', 'number'].forEach(req => {
            const elem = document.getElementById('req_' + req);
            elem.classList.remove('text-green-600', 'text-red-600');
            elem.classList.add('text-gray-500');
        });
    }

    // validate password requirements
    function validatePasswordRequirements(password) {
        const requirements = {
            length: password.length >= 12,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            special: /[^a-zA-Z0-9]/.test(password),
            number: /[0-9]/.test(password)
        };

        // update UI for each requirement
        Object.keys(requirements).forEach(req => {
            const elem = document.getElementById('req_' + req);
            elem.classList.remove('text-gray-500', 'text-green-600', 'text-red-600');
            if (password.length > 0) {
                elem.classList.add(requirements[req] ? 'text-green-600' : 'text-red-600');
            } else {
                elem.classList.add('text-gray-500');
            }
        });

        return Object.values(requirements).every(req => req);
    }

    // real-time validation for password
    document.addEventListener('DOMContentLoaded', function() {
        const oldPasswordInput = document.querySelector('input[name="old_password"]');
        const newPasswordInput = document.querySelector('input[name="new_password"]');
        const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');

        // validate old password against server
        if (oldPasswordInput) {
            let validationTimeout;
            oldPasswordInput.addEventListener('input', function() {
                const password = this.value;
                const checkMessage = document.getElementById('old_password_check');

                clearTimeout(validationTimeout);

                // remove existing icon if any
                const existingIcon = document.getElementById('old_password_icon');
                if (existingIcon) {
                    existingIcon.remove();
                }

                if (password.length === 0) {
                    checkMessage.textContent = '';
                    oldPasswordInput.classList.remove('!border-green-500', 'focus:!border-green-500',
                        'focus:!ring-green-100', '!border-red-500', 'focus:!border-red-500',
                        'focus:!ring-red-100');
                    return;
                }

                // debounce the validation request
                validationTimeout = setTimeout(() => {
                    fetch('{{ route('profile.validate-password') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                password: password
                            })
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            if (data.valid) {
                                checkMessage.textContent = '✓ Password verified';
                                checkMessage.className = 'text-xl text-green-600';

                                // green border
                                oldPasswordInput.classList.remove('!border-red-500',
                                    'focus:!border-red-500', 'focus:!ring-red-100');
                                oldPasswordInput.classList.add('!border-green-500',
                                    'focus:!border-green-500', 'focus:!ring-green-100');

                                // remove any existing icon first
                                const existingIcon = document.getElementById(
                                    'old_password_icon');
                                if (existingIcon) {
                                    existingIcon.remove();
                                }

                                // green check icon
                                const inputContainer = oldPasswordInput.closest(
                                    '.relative');
                                if (inputContainer) {
                                    const iconHtml = `<span id="old_password_icon" class="absolute right-16 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                                </span>`;
                                    inputContainer.insertAdjacentHTML('beforeend',
                                        iconHtml);
                                }
                            } else {
                                checkMessage.textContent = '✗ Incorrect password';
                                checkMessage.className = 'text-xl text-red-600';

                                // red border
                                oldPasswordInput.classList.remove('!border-green-500',
                                    'focus:!border-green-500', 'focus:!ring-green-100');
                                oldPasswordInput.classList.add('!border-red-500',
                                    'focus:!border-red-500', 'focus:!ring-red-100');

                                // remove any existing icon first
                                const existingIcon = document.getElementById(
                                    'old_password_icon');
                                if (existingIcon) {
                                    existingIcon.remove();
                                }

                                // red x icon
                                const inputContainer = oldPasswordInput.closest(
                                    '.relative');
                                if (inputContainer) {
                                    const iconHtml = `<span id="old_password_icon" class="absolute right-16 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                                </span>`;
                                    inputContainer.insertAdjacentHTML('beforeend',
                                        iconHtml);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error validating password:', error);
                        });
                }, 100);
            });
        }

        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                validatePasswordRequirements(this.value);
            });
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const newPassword = newPasswordInput.value;
                const confirmMatch = document.getElementById('confirm_password_match');

                if (this.value.length > 0) {
                    if (this.value === newPassword) {
                        confirmMatch.textContent = '✓ Passwords match';
                        confirmMatch.className = 'text-xl text-green-600';
                    } else {
                        confirmMatch.textContent = '✗ Passwords do not match';
                        confirmMatch.className = 'text-xl text-red-600';
                    }
                } else {
                    confirmMatch.textContent = '';
                }
            });
        }

        // handle form submission
        const form = document.getElementById('changePasswordForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const oldPassword = document.querySelector('input[name="old_password"]').value;
                const newPassword = document.querySelector('input[name="new_password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                const errorDiv = document.getElementById('password_error');

                // validate new password requirements
                if (!validatePasswordRequirements(newPassword)) {
                    errorDiv.querySelector('p').textContent = 'Please meet all password requirements';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                // check if passwords match
                if (newPassword !== confirmPassword) {
                    errorDiv.querySelector('p').textContent = 'Passwords do not match';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                // check if new password is different from old
                if (oldPassword === newPassword) {
                    errorDiv.querySelector('p').textContent =
                        'New password must be different from old password';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                errorDiv.classList.add('hidden');

                // submit
                fetch('{{ route('profile.change-password') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            old_password: oldPassword,
                            new_password: newPassword,
                            new_password_confirmation: confirmPassword
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closePasswordModal();
                            if (typeof showAlert === 'function') {
                                showAlert('success', 'Success', 'Password changed successfully!');
                            }
                        } else {
                            errorDiv.querySelector('p').textContent = data.message ||
                                'Failed to change password';
                            errorDiv.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        errorDiv.querySelector('p').textContent =
                            'An error occurred while changing your password';
                        errorDiv.classList.remove('hidden');
                    });
            });
        }
    });
</script>
