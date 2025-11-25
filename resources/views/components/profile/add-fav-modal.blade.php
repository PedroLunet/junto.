<div id="addFavModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 id="modalTitle" class="text-3xl font-bold text-gray-900">Add favorite</h2>
            <a id="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </a>
        </div>

        <!-- body -->
        <div class="flex-1 p-20">
        </div>


        <!-- footer -->
        <div class="flex justify-end gap-3 p-6">
            <button id="cancelBtn" class="px-4 py-2 transition-colors">
                Cancel
            </button>
            <button id="saveBtn"
                class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-500 transition-colors">
                Save
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addFavModal');
        const modalTitle = document.getElementById('modalTitle');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');

        // open modal with specific type
        window.openAddFavModal = function(type) {
            let title;

            switch (type) {
                case 'book':
                    title = 'Add favorite book';
                    break;
                case 'movie':
                    title = 'Add favorite movie';
                    break;
                case 'music':
                    title = 'Add favorite music';
                    break;
                default:
                    title = 'Add favorite';
            }

            modalTitle.textContent = title;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        function closeModalHandler() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // close modal event listeners
        closeModal.addEventListener('click', closeModalHandler);
        cancelBtn.addEventListener('click', closeModalHandler);

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
    });
</script>
