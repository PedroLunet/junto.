<!-- modal overlay -->
<div id="create-group-post-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full h-auto">
            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create New Group Post</h3>
            </div>
            <!-- modal body -->
            <div class="p-8">
                <form id="create-group-post-form" action="{{ route('groups.posts.store', ['group' => $group->id]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">What would you like to share with the
                            group?</label>
                        <textarea name="content"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]"
                            rows="4" placeholder="Share your thoughts..."></textarea>
                    </div>
                    <div id="group-image-preview-container" class="mb-4 hidden relative">
                        <img id="group-image-preview" src="#" alt="Preview"
                            class="max-h-64 rounded-lg object-cover border border-gray-200">
                        <x-ui.button type="button" id="remove-group-image-btn" variant="ghost">X</x-ui.button>
                    </div>
                    <div class="mb-4">
                        <input type="file" name="image" id="group-image-input" accept="image/*" class="hidden">
                        <x-ui.button type="button" id="add-group-image-button" variant="secondary">Add
                            Photo</x-ui.button>
                        <span id="group-file-name" class="ml-3 text-sm text-gray-600"></span>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <x-ui.button type="button" id="cancel-group-post-button"
                            variant="secondary">Cancel</x-ui.button>
                        <x-ui.button type="submit">Post to Group</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const createButton = document.getElementById('group-post-button');
        const modal = document.getElementById('create-group-post-modal');
        const cancelButton = document.getElementById('cancel-group-post-button');
        const textarea = document.querySelector('#create-group-post-modal textarea');
        const form = document.getElementById('create-group-post-form');
        const addImageButton = document.getElementById('add-group-image-button');
        const imageInput = document.getElementById('group-image-input');
        const fileName = document.getElementById('group-file-name');
        const previewContainer = document.getElementById('group-image-preview-container');
        const previewImage = document.getElementById('group-image-preview');
        const removeImageBtn = document.getElementById('remove-group-image-btn');

        if (createButton && modal) {
            createButton.addEventListener('click', function() {
                modal.style.display = 'block';
            });
        }

        // close modal if cancel button clicked
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                modal.style.display = 'none';
                if (textarea) {
                    textarea.value = '';
                }
                if (imageInput) {
                    imageInput.value = '';
                    fileName.textContent = '';
                    previewContainer.classList.add('hidden');
                    previewImage.src = '#';
                }
            });
        }

        // image button click
        if (addImageButton && imageInput) {
            addImageButton.addEventListener('click', function() {
                imageInput.click();
            });

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                    fileName.textContent = file.name;
                }

            });
        }

        // Remove selected image
        removeImageBtn.addEventListener('click', function() {
            imageInput.value = '';
            previewContainer.classList.add('hidden');
            previewImage.src = '#';
            fileName.textContent = '';
        });

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form)

                if (!imageInput || imageInput.files.length === 0) {
                    formData.delete('image');
                }

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.style.display = 'none';
                            textarea.value = '';
                            imageInput.value = '';
                            fileName.textContent = '';
                            previewContainer.classList.add('hidden');
                            previewImage.src = '#';

                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error creating group post:', error);
                    })
            })
        }

    });
</script>
