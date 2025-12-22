<form id="editProfileForm" class="space-y-6" method="POST" action="{{ route('profile.update') }}"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')


    <div class="flex flex-col gap-6 md:flex-row md:items-center md:gap-8">
        <!-- profile picture -->
        <div class="flex flex-col items-center mb-4 md:mb-0 md:mr-6 w-full md:w-auto">
            <div class="shrink-0 w-32 h-32 sm:w-36 sm:h-36 md:w-44 md:h-44 lg:w-48 lg:h-48 relative mb-3">
                <div class="w-full h-full rounded-full overflow-hidden relative">
                    <img id="profileImagePreview"
                        src="{{ $user->profilepicture ? asset('profile/' . $user->profilepicture) : asset('profile/default.png') }}"
                        alt="Profile Picture" class="absolute inset-0 w-full h-full object-cover"
                        onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
                </div>
                <!-- Profile image upload button and hidden file input -->
                <input type="file" id="profileImageInput" name="profilePicture" accept="image/*" class="hidden" />
                <x-ui.icon-button type="button" id="editProfileImageBtn" variant="blue" title="Upload photo"
                    class="absolute -top-0.5 -right-0.5 rounded-full flex items-center justify-center text-base md:text-lg font-bold z-10 p-3 shadow-lg">
                    <i class="fas fa-pencil"></i>
                </x-ui.icon-button>
                <x-ui.icon-button type="button" id="resetProfileImageBtn" variant="red" title="Reset to Default"
                    class="absolute -top-0.5 -left-0.5 rounded-full flex items-center justify-center text-base md:text-lg font-bold z-10 p-3 shadow-lg">
                    <i class="fas fa-trash"></i>
                </x-ui.icon-button>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 text-center max-w-[180px] sm:max-w-[200px]">
                Supported formats: JPG, JPEG, PNG<br>
                Max size: 2MB
            </p>
        </div>

        <div class="flex-1 w-full space-y-4 sm:space-y-6">
            <!-- name -->
            <x-ui.input label="Name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}"
                :error="$errors->first('name')" class="w-full" />

            <!-- username -->
            <x-ui.input label="Username" name="username" type="text"
                value="{{ old('username', $user->username ?? '') }}" :error="$errors->first('username')" class="w-full" />

            <!-- email -->
            <x-ui.input label="Email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}"
                :error="$errors->first('email')" id="editEmailInput" class="w-full" />
        </div>
    </div>




    <!-- bio -->
    <div class="w-full mt-2 mb-4">
        <x-ui.input label="Bio" name="bio" type="textarea" value="{{ old('bio', $user->bio ?? '') }}"
            placeholder="Tell others about yourself..." rows="4" :error="$errors->first('bio')"
            help="Write a short description about yourself that will be visible on your profile." class="w-full" />
    </div>

    <!-- Save Button -->
    <div class="flex justify-end w-full mt-2">
        <x-ui.button type="submit" variant="primary" class="text-base w-full sm:w-auto" id="saveProfileBtn">
            Save Changes
        </x-ui.button>
    </div>

</form>
<div id="profileUpdateAlertContainer"
    class="fixed top-4 right-2 sm:top-6 sm:right-6 z-50 flex flex-col gap-2 sm:gap-4 items-end w-[90vw] max-w-xs sm:max-w-sm md:max-w-md">
</div>

<script>
    function openChangeEmailModal() {
        document.getElementById('changeEmailModal').classList.remove('hidden');
        document.getElementById('newEmailInput').value = '';
        document.getElementById('currentPasswordInput').value = '';
        document.getElementById('changeEmailError').classList.add('hidden');
    }

    function closeChangeEmailModal() {
        document.getElementById('changeEmailModal').classList.add('hidden');
        document.getElementById('newEmailInput').value = '';
        document.getElementById('currentPasswordInput').value = '';
        document.getElementById('changeEmailError').classList.add('hidden');
    }

    function confirmChangeEmail() {
        const newEmail = document.getElementById('newEmailInput').value;
        const password = document.getElementById('currentPasswordInput').value;
        const errorDiv = document.getElementById('changeEmailError');
        errorDiv.classList.add('hidden');
        if (!newEmail || !password) {
            errorDiv.textContent = 'Please enter both email and password.';
            errorDiv.classList.remove('hidden');
            return;
        }
        fetch('/profile/change-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: newEmail,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('editEmailInput').value = newEmail;
                    closeChangeEmailModal();
                } else {
                    errorDiv.textContent = data.message || 'Failed to change email.';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(() => {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
            });
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Change email button logic
        document.getElementById('changeEmailBtn').addEventListener('click', openChangeEmailModal);
        const form = document.getElementById('editProfileForm');
        const saveBtn = document.getElementById('saveProfileBtn');
        const successDiv = document.getElementById('profileUpdateSuccess');
        const errorDiv = document.getElementById('profileUpdateError');

        // Profile image upload logic
        const editProfileImageBtn = document.getElementById('editProfileImageBtn');
        const profileImageInput = document.getElementById('profileImageInput');
        const profileImagePreview = document.getElementById('profileImagePreview');
        const resetProfileImageBtn = document.getElementById('resetProfileImageBtn');

        // Default image path
        const defaultImagePath = "{{ asset('profile/default.png') }}";
        // Reset profile image to default on trash button click
        resetProfileImageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            profileImagePreview.src = defaultImagePath;
            profileImageInput.value = '';
            // Mark that the image should be reset to default
            form.setAttribute('data-reset-profile-picture', 'true');
        });

        editProfileImageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            profileImageInput.click();
        });

        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    profileImagePreview.src = ev.target.result;
                };
                reader.readAsDataURL(file);
                // Unset reset flag if a new image is chosen
                form.removeAttribute('data-reset-profile-picture');
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            successDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            const formData = new FormData(form);
            // Remove profilePicture if no file is selected
            if (!profileImageInput.files[0]) {
                formData.delete('profilePicture');
            }
            // If reset to default, send a flag
            if (form.getAttribute('data-reset-profile-picture') === 'true') {
                formData.append('reset_profile_picture', '1');
            }

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
                        // Determine which fields were updated for specific alerts
                        let updatedFields = data.updatedFields || [];
                        let alertTitle = 'Profile updated';
                        let alertMessage = '';
                        if (updatedFields.length > 1) {
                            alertTitle = 'Profile details updated';
                            alertMessage = 'Your profile details were successfully updated.';
                        } else if (updatedFields.length === 1) {
                            switch (updatedFields[0]) {
                                case 'username':
                                    alertTitle = 'Username updated';
                                    alertMessage = 'Your username was successfully updated.';
                                    break;
                                case 'email':
                                    alertTitle = 'Email updated';
                                    alertMessage = 'Your email address was successfully updated.';
                                    break;
                                case 'name':
                                    alertTitle = 'Name updated';
                                    alertMessage = 'Your name was successfully updated.';
                                    break;
                                case 'bio':
                                    alertTitle = 'Bio updated';
                                    alertMessage = 'Your bio was successfully updated.';
                                    break;
                                case 'profilePicture':
                                    alertTitle = 'Profile picture updated';
                                    alertMessage = 'Your profile picture was successfully updated.';
                                    break;
                                default:
                                    alertTitle = 'Profile updated';
                                    alertMessage = 'Your profile was successfully updated.';
                            }
                        } else {
                            alertTitle = 'Profile updated';
                            alertMessage = data.message || 'Your profile was successfully updated.';
                        }
                        showProfileAlert('success', alertTitle, alertMessage);
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showProfileAlert('error', 'Update failed', data.message ||
                            'An error occurred while updating your profile');
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
                                const iconHtml = `<span class="absolute right-4 ${input.tagName === 'TEXTAREA' ? 'top-4' : ''} flex items-center justify-center pointer-events-none input-error-icon">
                                    <i class="fas fa-exclamation-circle text-red-500 text-3xl"></i>
                                </span>`;
                                input.parentElement.insertAdjacentHTML('beforeend',
                                    iconHtml);

                                // Add error message
                                const errorMsg =
                                    `<p class="mt-2 text-xl text-red-500 font-medium input-error-message">${error.errors[field][0]}</p>`;
                                input.parentElement.parentElement.insertAdjacentHTML(
                                    'beforeend', errorMsg);

                                // Add input event listener to clear error on typing
                                input.addEventListener('input', function clearError() {
                                    // Remove error styling
                                    input.classList.remove('border-red-500',
                                        'focus:border-red-500',
                                        'focus:ring-red-100', 'pr-12',
                                        '!border-red-500');
                                    input.classList.add('border-gray-300');

                                    // Remove error icon
                                    const errorIcon = input.parentElement
                                        .querySelector('.input-error-icon');
                                    if (errorIcon) errorIcon.remove();

                                    // Remove error message
                                    const errorMessage = input.parentElement
                                        .parentElement.querySelector(
                                            '.input-error-message');
                                    if (errorMessage) errorMessage.remove();

                                    // Remove this event listener after first use
                                    input.removeEventListener('input', clearError);
                                }, {
                                    once: false
                                });
                            }
                        });
                        showProfileAlert('error', 'Validation error',
                            'Please fix the highlighted fields.');
                    } else {
                        showProfileAlert('error', 'Update failed', error.message ||
                            'An error occurred while updating your profile.');
                    }
                })
            // Show alert-card on top right
            function showProfileAlert(type, title, message) {
                fetch('/profile/render-alert', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'text/html'
                        },
                        body: JSON.stringify({
                            type,
                            title,
                            message,
                            id: 'profile-update-alert-' + Date.now()
                        })
                    })
                    .then(response => response.text())
                    .then(html => {
                        const container = document.getElementById('profileUpdateAlertContainer');
                        if (container) {
                            const wrapper = document.createElement('div');
                            wrapper.innerHTML = html;
                            container.appendChild(wrapper);
                            setTimeout(() => {
                                wrapper.style.opacity = '0';
                                setTimeout(() => wrapper.remove(), 600);
                            }, 3000);
                        }
                    });
            }
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
            });
        });
    });
</script>
