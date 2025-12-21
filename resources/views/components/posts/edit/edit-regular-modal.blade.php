<!-- edit modal overlay -->
<div id="edit-post-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full h-auto">

            <!-- modal header -->
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-2xl font-semibold">Edit Post</h3>
            </div>

            <!-- modal body -->
            <div class="p-6">
                <form id="edit-post-form" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-post-id" name="post_id">

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">What would you like to share?</label>
                        <textarea id="edit-content" name="content"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]"
                            rows="4" placeholder="Share your thoughts..."></textarea>
                    </div>

                    <div class="mb-4" id="current-image-container" style="display: none;">
                        <label class="block font-medium text-gray-700 mb-2">Current Image:</label>
                        <div class="relative inline-block">
                            <img id="current-image" src="" alt="current image" class="max-w-xs h-auto rounded">
                            <x-ui.button type="button" id="remove-current-image"
                                variant="danger" class="absolute top-2 right-2 !px-2 !py-1 text-xs">X</x-ui.button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <input type="file" name="image" id="edit-image-input" accept="image/*" class="hidden">
                        <x-ui.button type="button" id="edit-add-image-button" variant="secondary">Add Photo</x-ui.button>
                        <span id="edit-file-name" class="ml-3 text-sm text-gray-600"></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <x-ui.button type="button" id="delete-button" variant="danger">Delete</x-ui.button>
                        <div class="flex space-x-3">
                            <x-ui.button type="button" id="edit-cancel-button" variant="secondary">Cancel</x-ui.button>
                            <x-ui.button type="submit">Update Post</x-ui.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('edit-post-modal');
        const editCancelButton = document.getElementById('edit-cancel-button');
        const editTextarea = document.getElementById('edit-content');
        const editForm = document.getElementById('edit-post-form');
        const editAddImageButton = document.getElementById('edit-add-image-button');
        const editImageInput = document.getElementById('edit-image-input');
        const editFileName = document.getElementById('edit-file-name');
        const currentImageContainer = document.getElementById('current-image-container');
        const currentImage = document.getElementById('current-image');
        const removeCurrentImageBtn = document.getElementById('remove-current-image');
        const editPostIdInput = document.getElementById('edit-post-id');
        const deleteButton = document.getElementById('delete-button');

        window.openEditModal = function(postId, content, imagePath = null) {
            editPostIdInput.value = postId;
            editTextarea.value = content;
            editForm.action = `/posts/${postId}`;

            if (imagePath) {
                currentImage.src = imagePath;
                currentImageContainer.style.display = 'block';
            } else {
                currentImageContainer.style.display = 'none';
            }

            editModal.style.display = 'block';
        };

        if (editCancelButton) {
            editCancelButton.addEventListener('click', function() {
                editModal.style.display = 'none';
                resetEditForm();
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this post?')) {
                    const postId = editPostIdInput.value;
                    fetch(`/posts/${postId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editModal.style.display = 'none';
                                window.location.reload();
                            } else {
                                alert(data.message || 'Error deleting post');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting post:', error);
                            alert('An error occurred while deleting the post.');
                        });
                }
            });
        }

        if (editAddImageButton && editImageInput) {
            editAddImageButton.addEventListener('click', function() {
                editImageInput.click();
            });

            editImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    editFileName.textContent = file.name;

                    // update the preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        currentImage.src = e.target.result;
                        currentImageContainer.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        if (removeCurrentImageBtn) {
            removeCurrentImageBtn.addEventListener('click', function() {
                currentImageContainer.style.display = 'none';

                const removeImageInput = document.createElement('input');
                removeImageInput.type = 'hidden';
                removeImageInput.name = 'remove_image';
                removeImageInput.value = '1';
                editForm.appendChild(removeImageInput);
            });
        }


        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(editForm);

                fetch(editForm.action, {
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
                            editModal.style.display = 'none';
                            resetEditForm();
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error updating post:', error);
                    });
            });
        }

        function resetEditForm() {
            editTextarea.value = '';
            editImageInput.value = '';
            editFileName.textContent = '';
            currentImageContainer.style.display = 'none';
            // remove any hidden remove_image inputs
            const removeImageInputs = editForm.querySelectorAll('input[name="remove_image"]');
            removeImageInputs.forEach(input => input.remove());
        }
    });
</script>
