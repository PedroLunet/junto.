<div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 class="text-3xl font-bold text-gray-900">Edit Profile</h2>
            <a id="closeEditProfileModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </a>
        </div>

        <!-- body -->
        <div class="flex-1 px-8">
            <form id="editProfileForm" class="space-y-6">
                @csrf

                <!-- name -->
                <div>
                    <label for="editName" class="block text-lg font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" id="editName" name="name" value="{{ $user->name ?? '' }}"
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                </div>

                <!-- username -->
                <div>
                    <label for="editUsername" class="block text-lg font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" id="editUsername" name="username" value="{{ $user->username ?? '' }}"
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                </div>

                <!-- bio -->
                <div>
                    <label for="editBio" class="block text-lg font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="editBio" name="bio" rows="4" placeholder="Tell others about yourself..."
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent resize-none">{{ $user->bio ?? '' }}</textarea>
                </div>
            </form>
        </div>

        <!-- footer -->
        <div class="flex justify-end gap-4 p-8">
            <button type="button" onclick="closeEditProfileModal()"
                class="px-8 py-3 rounded-lg text-lg font-medium transition-colors">
                Cancel
            </button>
            <button type="submit" form="editProfileForm"
                class="bg-[#38157a] text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-[#2d1060] transition-colors">
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editProfileModal');
        const closeModal = document.getElementById('closeEditProfileModal');
        const form = document.getElementById('editProfileForm');

        function closeModalHandler() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // make closeModalHandler available globally
        window.closeEditProfileModal = closeModalHandler;

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
        window.openEditProfileModal = function() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // focus the name input after a short delay
            setTimeout(() => {
                document.getElementById('editName').focus();
            }, 100);
        };

    });
</script>
