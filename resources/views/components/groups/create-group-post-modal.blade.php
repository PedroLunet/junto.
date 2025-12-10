<!-- Group Post Modal -->
<div id="create-group-post-modal" class="fixed inset-0 z-50 hidden" onclick="closeGroupPostModal()">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-[90vw] h-[85vh] flex flex-col">
                <form id="create-group-post-form" action="{{ route('groups.posts.store', ['group' => $group->id]) }}" method="POST" enctype="multipart/form-data" class="flex flex-col h-full">
                    @csrf
                    <div class="p-8 flex-1 overflow-y-auto">
                        <textarea name="content" rows="5" class="w-full border-gray-300 rounded-lg p-4 text-xl focus:ring-2 focus:ring-[#38157a] focus:border-transparent" placeholder="Write something for the group..."></textarea>
                        <input type="hidden" name="group_id" value="{{ $group->id }}">
                        <div class="mt-4 flex items-center gap-4">
                            <button type="button" id="add-group-image-button" class="px-4 py-2 bg-gray-200 rounded">Add Image</button>
                            <input type="file" id="group-image-input" name="image" class="hidden">
                            <span id="group-file-name" class="text-gray-500"></span>
                        </div>
                        <div id="group-image-preview-container" class="mt-4 hidden">
                            <img id="group-image-preview" src="#" alt="Preview" class="max-w-xs rounded-lg shadow">
                            <button type="button" id="remove-group-image-btn" class="ml-2 px-2 py-1 bg-red-200 rounded">Remove</button>
                        </div>
                    </div>
                    <div class="p-8 border-t border-gray-200 flex justify-end gap-4">
                        <button type="button" id="cancel-group-post-button" class="px-6 py-2 bg-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-[#38157a] text-white rounded">Post to Group</button>
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
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            modal.style.display = 'none';
            if (textarea) textarea.value = '';
            if (imageInput) {
                imageInput.value = '';
                fileName.textContent = '';
                previewContainer.classList.add('hidden');
                previewImage.src = '#';
            }
        });
    }
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
    removeImageBtn.addEventListener('click', function() {
        imageInput.value = '';
        previewContainer.classList.add('hidden');
        previewImage.src = '#';
        fileName.textContent = '';
    });
    if (form) {
        form.addEventListener('submit', function(e) {
            // Optionally add AJAX logic here
        });
    }
});
function closeGroupPostModal() {
    document.getElementById('create-group-post-modal').style.display = 'none';
}
</script>
