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
                        <x-ui.input label="Old Password" type="password" name="old_password" id="old_password"
                            placeholder="Enter your old password" required />

                        <!-- New Password -->
                        <div>
                            <x-ui.input label="New Password" type="password" name="new_password" id="new_password"
                                placeholder="Enter your new password" required />
                            <div id="password_requirements" class="mt-3 space-y-1 text-sm">
                                <p class="text-gray-600 font-medium mb-2 text-xl">Please add all necessary characters to
                                    create
                                    safe password.</p>
                                <p id="req_length" class="text-gray-500 text-xl"><span class="mr-2">•</span>Minimum
                                    characters
                                    12</p>
                                <p id="req_uppercase" class="text-gray-500 text-xl"><span class="mr-2">•</span>One
                                    uppercase
                                    character</p>
                                <p id="req_lowercase" class="text-gray-500 text-xl"><span class="mr-2">•</span>One
                                    lowercase
                                    character</p>
                                <p id="req_special" class="text-gray-500 text-xl"><span class="mr-2">•</span>One
                                    special
                                    character</p>
                                <p id="req_number" class="text-gray-500 text-xl"><span class="mr-2">•</span>One number
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
                        <x-ui.button type="submit" variant="primary" class="w-full text-2xl">
                            Change Password
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Open password modal
    window.openPasswordModal = function() {
        document.getElementById('changePasswordModal').classList.remove('hidden');
        document.getElementById('changePasswordForm').reset();
        clearPasswordValidation();
    };

    // Close password modal
    window.closePasswordModal = function() {
        document.getElementById('changePasswordModal').classList.add('hidden');
        document.getElementById('changePasswordForm').reset();
        clearPasswordValidation();
    };

    // Clear validation messages
    function clearPasswordValidation() {
        document.getElementById('confirm_password_match').textContent = '';
        document.getElementById('password_error').classList.add('hidden');

        // Reset requirement styles
        ['length', 'uppercase', 'lowercase', 'special', 'number'].forEach(req => {
            const elem = document.getElementById('req_' + req);
            elem.classList.remove('text-green-600', 'text-red-600');
            elem.classList.add('text-gray-500');
        });
    }

    // Validate password requirements
    function validatePasswordRequirements(password) {
        const requirements = {
            length: password.length >= 12,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
            number: /[0-9]/.test(password)
        };

        // Update UI for each requirement
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

    // Real-time validation for new password
    document.addEventListener('DOMContentLoaded', function() {
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

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
                        confirmMatch.className = 'mt-2 text-sm text-green-600';
                    } else {
                        confirmMatch.textContent = '✗ Passwords do not match';
                        confirmMatch.className = 'mt-2 text-sm text-red-600';
                    }
                } else {
                    confirmMatch.textContent = '';
                }
            });
        }

        // Handle form submission
        const form = document.getElementById('changePasswordForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const oldPassword = document.getElementById('old_password').value;
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const errorDiv = document.getElementById('password_error');

                // Validate new password requirements
                if (!validatePasswordRequirements(newPassword)) {
                    errorDiv.querySelector('p').textContent = 'Please meet all password requirements';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                // Check if passwords match
                if (newPassword !== confirmPassword) {
                    errorDiv.querySelector('p').textContent = 'Passwords do not match';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                // Check if new password is different from old
                if (oldPassword === newPassword) {
                    errorDiv.querySelector('p').textContent =
                        'New password must be different from old password';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                errorDiv.classList.add('hidden');

                // Submit to server
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
                            alert('Password changed successfully!');
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
