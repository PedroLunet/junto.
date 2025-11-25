
<!-- modal overlay -->
<div id="create-movie-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full h-auto">

            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Movie Review</h3>
            </div>
            
            <!-- modal body -->
            <div class="p-8">
                <form id="create-movie-review-form" method="POST">
                    @csrf
                    <div class="mb-4">
                        
                        <textarea name="content" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]" rows="4" placeholder="Write your review..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-movie-review-button" class="px-4 py-2 text-gray-800 border border-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-[#38157a] text-white rounded hover:bg-[#7455ad]">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function (){
        const createButton = document.getElementById('movie-button');
        const modal = document.getElementById('create-movie-review-modal');
        const cancelButton = document.getElementById('cancel-movie-review-button');
        const textarea = document.querySelector('#create-movie-review-modal textarea');
        const form = document.getElementById('create-movie-review-form');
                
        if (createButton && modal) {
            createButton.addEventListener('click', function() {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            });
        }

        // close modal if cancel button clicked
        if (cancelButton){
            cancelButton.addEventListener('click', function (){
                modal.style.display = 'none';
                modal.classList.add('hidden');
                if (textarea) {
                    textarea.value = '';
                }
            });
        }

        // Close modal if clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        });
    });
</script>
