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
                        <div class="relative">
                            <textarea id="group-post-content" name="content"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]"
                                rows="4" placeholder="Share your thoughts... (type @ to mention users)"></textarea>
                            <div id="group-tag-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto z-50">
                            </div>
                        </div>
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
        const textarea = document.getElementById('group-post-content');
        const form = document.getElementById('create-group-post-form');
        const tagDropdown = document.getElementById('group-tag-dropdown');
        const addImageButton = document.getElementById('add-group-image-button');
        const imageInput = document.getElementById('group-image-input');
        const fileName = document.getElementById('group-file-name');
        const previewContainer = document.getElementById('group-image-preview-container');
        const previewImage = document.getElementById('group-image-preview');
        const removeImageBtn = document.getElementById('remove-group-image-btn');

        let selectedTags = [];

        if (createButton && modal) {
            createButton.addEventListener('click', function() {
                modal.style.display = 'block';
            });
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                modal.style.display = 'none';
                if (textarea) {
                    textarea.value = '';
                }
                selectedTags = [];
                if (imageInput) {
                    imageInput.value = '';
                    fileName.textContent = '';
                    previewContainer.classList.add('hidden');
                    previewImage.src = '#';
                }
            });
        }

        if (textarea) {
            textarea.addEventListener('input', function() {
                const cursorPos = this.selectionStart;
                const textBeforeCursor = this.value.substring(0, cursorPos);
                const lastAtIndex = textBeforeCursor.lastIndexOf('@');

                if (lastAtIndex !== -1 && (lastAtIndex === 0 || /\s/.test(textBeforeCursor[lastAtIndex - 1]))) {
                    const afterAt = textBeforeCursor.substring(lastAtIndex + 1);
                    // Only show dropdown if there's no space after @ (still typing the mention)
                    if (afterAt.length > 0 && !afterAt.includes(' ')) {
                        searchUsers(afterAt);
                        tagDropdown.classList.remove('hidden');
                    } else {
                        tagDropdown.classList.add('hidden');
                    }
                } else {
                    tagDropdown.classList.add('hidden');
                }
            });
        }

        function searchUsers(query) {
            fetch(`/search-users?query=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                tagDropdown.innerHTML = '';
                if (data.users && data.users.length) {
                    data.users.forEach(user => {
                        const userDiv = document.createElement('div');
                        userDiv.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b flex items-center gap-2';
                        userDiv.innerHTML = `
                            <div class="w-6 h-6 rounded-full bg-[#38157a]/10 flex items-center justify-center text-[#38157a] font-bold text-xs shrink-0">
                                ${user.name.charAt(0)}
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="font-bold text-xs text-gray-900 block truncate">${user.name}</span>
                                <span class="text-gray-500 text-[10px]">@${user.username}</span>
                            </div>
                        `;
                        userDiv.addEventListener('click', function(e) {
                            e.preventDefault();
                            const cursorPos = textarea.selectionStart;
                            const textBeforeCursor = textarea.value.substring(0, cursorPos);
                            const atPos = textBeforeCursor.lastIndexOf('@');
                            
                            if (atPos !== -1) {
                                const beforeMention = textarea.value.substring(0, atPos);
                                const afterMention = textarea.value.substring(cursorPos);
                                const mention = '@' + user.name;
                                
                                textarea.value = beforeMention + mention + ' ' + afterMention;
                                
                                if (!selectedTags.some(t => t.id === user.id)) {
                                    selectedTags.push({ id: user.id, name: user.name });
                                }
                            }
                            
                            tagDropdown.classList.add('hidden');
                        });
                        tagDropdown.appendChild(userDiv);
                    });
                } else {
                    tagDropdown.innerHTML = '<span class="text-gray-400 text-xs p-3 block">No users found</span>';
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
                e.preventDefault();

                const formData = new FormData(form);

                if (selectedTags.length > 0) {
                    selectedTags.forEach((tag, index) => {
                        formData.append(`tags[${index}]`, tag.id);
                    });
                }

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
                        selectedTags = [];

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
