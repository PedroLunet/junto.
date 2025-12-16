@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-20 gap-10">
            <a href="{{ route('profile.show', $user->username) }}" class="mr-4 text-gray-600 hover:text-gray-800 p-3">
                <i class="fas fa-arrow-left text-3xl"></i>
            </a>
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Edit Profile</h1>
            </div>
        </div>
        <form id="editProfileForm" class="space-y-6" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-6">
                <!-- profile picture -->
                <div class="shrink-0 w-40 h-40 relative">
                    <div class="w-full h-full rounded-full overflow-hidden relative">
                        <img id="profileImagePreview" src="{{ $user->profile_picture ?? asset('profile/default.png') }}"
                            alt="Profile Picture" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                    <!-- Profile image upload button and hidden file input -->
                    <input type="file" id="profileImageInput" name="profile_picture" accept="image/*" class="hidden" />
                    <x-ui.button type="button" id="editProfileImageBtn" variant="outline"
                        class="absolute -top-2 -right-2 w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                        <i class="fas fa-pencil text-purple-500"></i>
                    </x-ui.button>
                    <x-ui.button type="button" id="resetProfileImageBtn" variant="outline"
                        class="absolute -top-2 -left-2 w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                        <i class="fas fa-trash text-red-500"></i>
                    </x-ui.button>
                </div>

                <div class="flex-1 space-y-6">
                    <!-- name -->
                    <div>
                        <label for="editName" class="block text-2xl font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" id="editName" name="name" value="{{ old('name', $user->name ?? '') }}"
                            class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                    </div>

                    <!-- username -->
                    <div>
                        <label for="editUsername" class="block text-2xl font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="editUsername" name="username"
                            value="{{ old('username', $user->username ?? '') }}"
                            class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- bio -->
            <div>
                <label for="editBio" class="block text-2xl font-medium text-gray-700 mb-2">Bio</label>
                <textarea id="editBio" name="bio" rows="4" placeholder="Tell others about yourself..."
                    class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent resize-none">{{ old('bio', $user->bio ?? '') }}</textarea>
            </div>

            <!-- password (fake input, styled like others) -->
            <div>
                <label class="block text-2xl font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <div class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-[#38157a] focus:border-transparent flex items-center select-none pr-14"
                        style="letter-spacing: 0.3em;">
                        <span aria-label="Password hidden" class="tracking-widest text-gray-500 flex-1">
                            <span
                                class="inline-block mx-0.5 text-3xl align-middle">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                        </span>
                        <x-ui.button variant="outline"
                            class="absolute right-2 top-0 bottom-0 my-auto h-12 w-12 p-2 flex items-center justify-center text-2xl font-bold px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100"
                            title="Edit Password">
                            <i
                                class="fas fa-pencil text-purple-500 leading-none align-middle flex items-center justify-center"></i>
                        </x-ui.button>
                    </div>
                </div>
            </div>

            <!-- form actions -->
            <div class="flex justify-end gap-4 pt-4">
                <x-ui.button type="submit" variant="primary" class="text-3xl" id="saveProfileBtn">
                    Save Changes
                </x-ui.button>
            </div>
        </form>
        <div id="profileUpdateSuccess" class="hidden mt-6 text-green-600 text-2xl font-semibold"></div>
        <div id="profileUpdateError" class="hidden mt-6 text-red-600 text-2xl font-semibold"></div>

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
                    }
                });

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';
                    successDiv.classList.add('hidden');
                    errorDiv.classList.add('hidden');

                    // Use FormData to support file upload
                    const formData = new FormData(form);
                    formData.set('name', form.name.value);
                    formData.set('username', form.username.value);
                    formData.set('bio', form.bio.value);
                    if (profileImageInput.files[0]) {
                        formData.set('profile_picture', profileImageInput.files[0]);
                    }

                    fetch(form.action, {
                            method: 'POST', // Use POST for file upload (with _method=PUT)
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
                                // If username changed, reload to the new edit profile page for the new username
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
    @endsection
