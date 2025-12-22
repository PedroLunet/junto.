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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editProfileForm');
        const saveBtn = document.getElementById('saveProfileBtn');

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
            // Remove profilePicture if no file is selected
            if (!profileImageInput.files[0]) {
                form.querySelector('input[name="profilePicture"]').remove();
            }
            // If reset to default, send a flag
            if (form.getAttribute('data-reset-profile-picture') === 'true') {
                // Already handled by input
            }
            form.submit();

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
        });
    });
</script>
