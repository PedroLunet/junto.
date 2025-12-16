<form id="editProfileForm" class="space-y-6" method="POST" action="{{ route('profile.update') }}"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="flex items-center gap-6">
        <!-- profile picture -->
        <div class="shrink-0 w-54 h-54 md:w-60 md:h-60 lg:w-72 lg:h-72 relative mr-10">
            <div class="w-full h-full rounded-full overflow-hidden relative">
                <img id="profileImagePreview"
                    src="{{ $user->profilepicture ? asset('profile/' . $user->profilepicture) : asset('profile/default.png') }}"
                    alt="Profile Picture" class="absolute inset-0 w-full h-full object-cover">
            </div>
            <!-- Profile image upload button and hidden file input -->
            <input type="file" id="profileImageInput" name="profilePicture" accept="image/*" class="hidden" />
            <x-ui.button type="button" id="editProfileImageBtn" variant="outline"
                class="absolute -top-2 -right-2 rounded-full flex items-center justify-center text-4xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                <i class="fas fa-pencil text-purple-500"></i>
            </x-ui.button>
            <x-ui.button type="button" id="resetProfileImageBtn" variant="outline"
                class="absolute -top-2 -left-2 rounded-full flex items-center justify-center text-4xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                <i class="fas fa-trash text-red-500"></i>
            </x-ui.button>
        </div>

        <div class="flex-1 space-y-6">
            <!-- name -->
            <div>
                <label for="editName" class="block text-3xl font-medium text-gray-700 mb-2">Name</label>
                <input type="text" id="editName" name="name" value="{{ old('name', $user->name ?? '') }}"
                    class="w-full px-4 py-3 text-3xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
            </div>

            <!-- username -->
            <div>
                <label for="editUsername" class="block text-3xl font-medium text-gray-700 mb-2">Username</label>
                <input type="text" id="editUsername" name="username"
                    value="{{ old('username', $user->username ?? '') }}"
                    class="w-full px-4 py-3 text-3xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
            </div>
        </div>
    </div>

    <!-- bio -->
    <div>
        <label for="editBio" class="block text-3xl font-medium text-gray-700 mb-2">Bio</label>
        <textarea id="editBio" name="bio" rows="4" placeholder="Tell others about yourself..."
            class="w-full px-4 py-3 text-3xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent resize-none">{{ old('bio', $user->bio ?? '') }}</textarea>
    </div>

    <!-- form actions -->
    <div class="flex justify-end gap-4 pt-4">
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.user && data.user.username) {
                            window.location.href = `/${data.user.username}/edit`;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        errorDiv.textContent = data.message ||
                            'An error occurred while updating your profile.';
                        errorDiv.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    errorDiv.textContent = 'An error occurred while updating your profile.';
                    errorDiv.classList.remove('hidden');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                });
        });
    });
</script>
