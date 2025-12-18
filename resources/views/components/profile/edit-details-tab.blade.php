<form id="editProfileForm" class="space-y-6" method="POST" action="{{ route('profile.update') }}"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="flex items-center gap-6">
        <!-- profile picture -->
        <div class="flex flex-col items-center mr-10">
            <div class="shrink-0 w-54 h-54 md:w-60 md:h-60 lg:w-72 lg:h-72 relative mb-4">
                <div class="w-full h-full rounded-full overflow-hidden relative">
                    <img id="profileImagePreview"
                        src="{{ $user->profilepicture ? asset('profile/' . $user->profilepicture) : asset('profile/default.png') }}"
                        alt="Profile Picture" class="absolute inset-0 w-full h-full object-cover"
                        onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}';">
                </div>
                <!-- Profile image upload button and hidden file input -->
                <input type="file" id="profileImageInput" name="profilePicture" accept="image/*" class="hidden" />
                <x-ui.button type="button" id="editProfileImageBtn" variant="outline" title="Upload photo"
                    class="absolute -top-2 -right-2 rounded-full flex items-center justify-center text-4xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                    <i class="fas fa-pencil text-[#820273]"></i>
                </x-ui.button>
                <x-ui.button type="button" id="resetProfileImageBtn" variant="outline" title="Reset to Default"
                    class="absolute -top-2 -left-2 rounded-full flex items-center justify-center text-4xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                    <i class="fas fa-trash text-red-500"></i>
                </x-ui.button>
            </div>
            <p class="text-sm text-gray-500 text-center max-w-[200px]">
                Supported formats: JPG, JPEG, PNG<br>
                Max size: 2MB
            </p>
        </div>

        <div class="flex-1 space-y-6">
            <!-- name -->
            <x-ui.input label="Name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}"
                :error="$errors->first('name')" />

            <!-- username -->
            <x-ui.input label="Username" name="username" type="text"
                value="{{ old('username', $user->username ?? '') }}" :error="$errors->first('username')" />
        </div>
    </div>

    <!-- bio -->
    <x-ui.input label="Bio" name="bio" type="textarea" value="{{ old('bio', $user->bio ?? '') }}"
        placeholder="Tell others about yourself..." rows="4" :error="$errors->first('bio')"
        help="Write a short description about yourself that will be visible on your profile." />

    <!-- Save Button -->
    <div class="flex justify-end">
        <x-ui.button type="submit" variant="primary" class="text-3xl" id="saveProfileBtn">
            Save Changes
        </x-ui.button>
    </div>
</form>
<div id="profileUpdateSuccess" class="hidden mt-6 text-green-600 text-3xl font-semibold"></div>
<div id="profileUpdateError" class="hidden mt-6 text-red-600 text-3xl font-semibold"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                        if (data.user && data.user.username) {
                            window.location.href = `/${data.user.username}/edit`;
                        } else {
                            window.location.reload();
                        }
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
                    } else {
                        errorDiv.textContent = error.message ||
                            'An error occurred while updating your profile.';
                        errorDiv.classList.remove('hidden');
                    }
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                });
        });
    });
</script>
