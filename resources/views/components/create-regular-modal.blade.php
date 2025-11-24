
<!-- modal overlay -->
<div id="create-regular-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full h-96">

            <!-- modal header -->
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-4xl font-semibold">Create New Post</h3>
            </div>
            
            <!-- modal body -->
            <div class="p-6">
                <form id="create-post-form" action="{{ route('posts.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">What would you like to share?</label>
                        <textarea name="content" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]" rows="4" placeholder="Share your thoughts..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-button" class="px-4 py-2 text-gray-800 border border-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-[#38157a] text-white rounded hover:bg-[#7455ad]">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function (){
        const createButton = document.getElementById('regular-button');
        const modal = document.getElementById('create-regular-modal');
        const cancelButton = document.getElementById('cancel-button');
        const textarea = document.querySelector('#create-regular-modal textarea');
        const form = document.getElementById('create-post-form');
                
        if (createButton && modal) {
            createButton.addEventListener('click', function() {
                modal.style.display = 'block';
                       
            });
        }

        // close modal if cancel button clicked
        if (cancelButton){
            cancelButton.addEventListener('click', function (){
                modal.style.display = 'none';
                if (textarea) {
                    textarea.value = '';
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(form)

                fetch (form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.style.display = 'none';
                        textarea.value = '';

                        window.location.reload();
                    }
                })
                .catch (error => {
                    console.error('Error creating post:', error);
                })
            })
        }

    });
</script>