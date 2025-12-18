<!-- modal overlay -->
<div id="create-regular-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full h-auto">

            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create New Post</h3>
            </div>

            <!-- modal body -->
            <div class="p-8">
                <form id="create-post-form" action="{{ route('posts.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">What would you like to share?</label>
                        <textarea
                            name="content"class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]"
                            rows="4" placeholder="Share your thoughts..."></textarea>
                    </div>

                    <div id="image-preview-container" class="mb-4 hidden relative">
                        <img id="image-preview" src="#" alt="Preview"
                            class="max-h-64 rounded-lg object-cover border border-gray-200">
                        <x-ui.button type="button" id="remove-image-btn" variant="ghost">X</x-ui.button>
                    </div>

                    <div class="mb-4">
                        <input type="file" name="image" id="image-input" accept=".jpg,.jpeg,.png,.gif"
                            class="hidden">
                        <div class="flex items-center">
                            <x-ui.button type="button" id="add-image-button" variant="secondary">Add
                                Photo</x-ui.button>
                            <span class="ml-3 text-sm text-gray-500">Supported: JPG, JPEG, PNG, GIF</span>
                        </div>
                        <span id="file-name" class="block mt-1 text-sm text-gray-600"></span>
                        <span id="file-size-error" class="block mt-1 text-sm text-red-600 hidden">File is too large.
                            Maximum size is 2MB.</span>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-ui.button type="button" id="cancel-button" variant="secondary">Cancel</x-ui.button>
                        <x-ui.button type="submit">Post</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const createButton = document.getElementById('regular-button');
        const modal = document.getElementById('create-regular-modal');
        const cancelButton = document.getElementById('cancel-button');
        const textarea = document.querySelector('#create-regular-modal textarea');
        const form = document.getElementById('create-post-form');

        const addImageButton = document.getElementById('add-image-button');
        const imageInput = document.getElementById('image-input');
        const fileName = document.getElementById('file-name');
        const previewContainer = document.getElementById('image-preview-container');
        const previewImage = document.getElementById('image-preview');
        const removeImageBtn = document.getElementById('remove-image-btn');

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
                const fileSizeError = document.getElementById('file-size-error');
                if (file) {
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        fileSizeError.classList.remove('hidden');
                        imageInput.value = '';
                        fileName.textContent = '';
                        previewContainer.classList.add('hidden');
                        previewImage.src = '#';
                        return;
                    } else {
                        fileSizeError.classList.add('hidden');
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                    fileName.textContent = file.name;
                } else {
                    fileSizeError.classList.add('hidden');
                }
            });
        }

        // 3. Remove selected image
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
                        console.error('Error creating post:', error);
                    })
            })
        }

    });
</script>
