
<!-- modal overlay -->
<div id="edit-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">

            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Edit Review</h3>
            </div>
            
            <!-- modal body -->
            <div class="p-8">
                <form id="edit-review-form" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- movie info -->
                    <div class="mb-6">
                        <div class="p-4 border rounded-lg bg-gray-50 flex items-start gap-4">
                            <img id="edit-review-movie-poster" src="" alt="Poster" class=" h-80 object-cover rounded shadow-sm hidden">
                            <div>
                                <h4 id="edit-review-movie-title" class="text-4xl font-bold text-gray-800"></h4>
                                <p id="edit-review-movie-director" class="text-gray-600"></p>
                                <p id="edit-review-movie-year" class="text-gray-600 text-xl"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="edit-star-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="edit-star-btn bg-transparent border-none p-0 h-auto leading-none shadow-none text-3xl text-gray-300 focus:text-gray-300 hover:text-yellow-400 hover:bg-transparent focus:bg-transparent transition-colors focus:outline-none" data-rating="{{ $i }}">
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="edit-rating-input" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">Write your review...</label>
                        <textarea name="content" id="edit-review-content" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]" rows="4"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-edit-review-button" class="px-4 py-2 text-gray-800 border border-gray-300 rounded-xl">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-[#38157a] text-white rounded-xl hover:bg-[#7455ad]">Update Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function (){
        const modal = document.getElementById('edit-review-modal');
        const cancelButton = document.getElementById('cancel-edit-review-button');
        const form = document.getElementById('edit-review-form');
        const ratingInput = document.getElementById('edit-rating-input');
        const starButtons = document.querySelectorAll('.edit-star-btn');

        // Rating logic
        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const rating = this.dataset.rating;
                ratingInput.value = rating;
                updateEditStars(rating);
            });

            button.addEventListener('mouseenter', function() {
                updateEditStars(this.dataset.rating);
            });

            button.addEventListener('mouseleave', function() {
                updateEditStars(ratingInput.value || 0);
            });
        });

        function updateEditStars(rating) {
            starButtons.forEach(btn => {
                const star = btn.querySelector('i');
                const btnRating = parseInt(btn.dataset.rating);
                const currentRating = parseInt(rating);
                
                if (btnRating <= currentRating) {
                    btn.classList.remove('text-gray-300', 'focus:text-gray-300');
                    btn.classList.add('text-yellow-400', 'focus:text-yellow-400');
                    star.classList.remove('fa-regular');
                    star.classList.add('fa-solid');
                } else {
                    btn.classList.add('text-gray-300', 'focus:text-gray-300');
                    btn.classList.remove('text-yellow-400', 'focus:text-yellow-400');
                    star.classList.add('fa-regular');
                    star.classList.remove('fa-solid');
                }
            });
        }

      
        window.openEditReviewModal = function(id, content, rating, movieTitle, moviePoster, movieYear, movieDirector) {
            const modal = document.getElementById('edit-review-modal');
            const form = document.getElementById('edit-review-form');
            const contentInput = document.getElementById('edit-review-content');
            const ratingInput = document.getElementById('edit-rating-input');
            const movieTitleEl = document.getElementById('edit-review-movie-title');
            const moviePosterEl = document.getElementById('edit-review-movie-poster');
            const movieYearEl = document.getElementById('edit-review-movie-year');
            const movieDirectorEl = document.getElementById('edit-review-movie-director');

            form.action = `/reviews/${id}`;
            contentInput.value = content;
            ratingInput.value = rating;
            movieTitleEl.textContent = movieTitle;
            movieYearEl.textContent = movieYear || '';
            movieDirectorEl.textContent = movieDirector || 'Unknown Director';
            
            if (moviePoster) {
                moviePosterEl.src = moviePoster;
                moviePosterEl.classList.remove('hidden');
            } else {
                moviePosterEl.classList.add('hidden');
            }
            
            updateEditStars(rating);
            
            modal.style.display = 'block';
            modal.classList.remove('hidden');
        };

        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        });

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                fetch(form.action, {
                    method: 'PUT',
                    body: JSON.stringify(data),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error updating review');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the review');
                });
            });
        }
    });
</script>
