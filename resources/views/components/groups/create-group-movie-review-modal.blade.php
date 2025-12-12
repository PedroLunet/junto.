<div id="create-group-movie-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Group Movie Review</h3>
            </div>
            <div class="p-8">
                <form id="create-group-movie-review-form" action="{{ route('groups.reviews.store', ['group' => $group->id, 'type' => 'movie']) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="movie">
                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                    
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">What movie did you watch?</label>
                        <div class="relative" id="groupMovieSearchContainer">
                            <input type="text" id="groupModalMovieSearch" placeholder="Search for a movie..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
                                autocomplete="off">
                            <div id="groupModalMovieSearchResults"
                                class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1">
                            </div>
                        </div>
                        
                        <div id="groupModalSelectedMovie"
                            class="hidden mt-4 p-4 border rounded-lg bg-gray-50 flex items-start gap-4 relative">
                            <input type="hidden" name="tmdb_id" id="groupSelectedMovieId">
                            <img id="groupSelectedMoviePoster" src="" alt="Poster"
                                class="h-40 w-28 object-cover rounded shadow-sm">
                            <div class="flex-1">
                                <h4 id="groupSelectedMovieTitle" class="text-2xl font-bold text-gray-800"></h4>
                                <p id="groupSelectedMovieYear" class="text-gray-500 text-sm mt-1"></p>
                            </div>
                            <button type="button" id="groupRemoveMovieBtn"
                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500 p-2">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="group-movie-star-rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    class="group-movie-star-btn text-3xl text-gray-300 hover:text-yellow-400 focus:outline-none transition-colors transform active:scale-95"
                                    data-rating="{{ $i }}">
                                    <i class="fas fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="group-movie-rating-input" required>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Your Review</label>
                        <textarea name="content" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent" rows="5" placeholder="Share your thoughts..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-ui.button type="button" id="cancel-group-movie-review-button" variant="secondary">Cancel</x-ui.button>
                        <x-ui.button type="submit">Post Review</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const movieForm = document.getElementById('create-group-movie-review-form');
    const movieModal = document.getElementById('create-group-movie-review-modal');
    if (movieForm && movieModal) {
        movieForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(movieForm);
            fetch(movieForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    movieModal.classList.add('hidden');
                    movieModal.style.display = 'none';
                    movieForm.reset();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error creating review');
                }
            })
            .catch(() => { alert('An error occurred while posting the review'); });
        });
    }
    const openBtn = document.getElementById('group-movie-review-button');
    const modal = document.getElementById('create-group-movie-review-modal');
    const cancelBtn = document.getElementById('cancel-group-movie-review-button');
    const searchInput = document.getElementById('groupModalMovieSearch');
    const resultsDiv = document.getElementById('groupModalMovieSearchResults');
    const searchContainer = document.getElementById('groupMovieSearchContainer');
    const selectedDiv = document.getElementById('groupModalSelectedMovie');
    const selectedId = document.getElementById('groupSelectedMovieId');
    const selectedTitle = document.getElementById('groupSelectedMovieTitle');
    const selectedYear = document.getElementById('groupSelectedMovieYear');
    const selectedPoster = document.getElementById('groupSelectedMoviePoster');
    const removeBtn = document.getElementById('groupRemoveMovieBtn');
    const starBtns = document.querySelectorAll('.group-movie-star-btn');
    const ratingInput = document.getElementById('group-movie-rating-input');

    if (openBtn && modal) {
        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        });
    }

    if (cancelBtn && modal) {
        cancelBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        });
    }

    let timeoutId;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();
            if (query.length < 2) { resultsDiv.classList.add('hidden'); return; }

            timeoutId = setTimeout(() => {
                fetch(`/movies/search?q=${encodeURIComponent(query)}`, { headers: { 'Accept': 'application/json' } })
                .then(response => response.json())
                .then(movies => {
                    resultsDiv.innerHTML = '';
                    if (movies.length === 0) {
                        resultsDiv.innerHTML = '<div class="p-3 text-gray-500">No movies found</div>';
                    } else {
                        movies.slice(0, 5).forEach(movie => {
                            const div = document.createElement('div');
                            div.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b flex items-center transition-colors';
                            
                            const safeYear = movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A';
                            const posterUrl = movie.poster_path ? `https://image.tmdb.org/t/p/w92${movie.poster_path}` : '';

                            div.innerHTML = `
                                <div class="flex items-center w-full">
                                    ${posterUrl ? `<img src="${posterUrl}" class="w-10 h-14 object-cover rounded mr-3">` : `<div class="w-10 h-14 bg-gray-200 rounded mr-3 flex items-center justify-center text-xs text-gray-500">No Img</div>`}
                                    <div><div class="font-medium text-gray-800">${movie.title}</div><div class="text-xs text-gray-500">${safeYear}</div></div>
                                </div>
                            `;

                            div.addEventListener('click', () => {
                                selectedId.value = movie.id;
                                selectedTitle.textContent = movie.title;
                                selectedYear.textContent = safeYear;
                                selectedPoster.src = posterUrl || '';
                                searchContainer.classList.add('hidden');
                                resultsDiv.classList.add('hidden');
                                selectedDiv.classList.remove('hidden');
                            });
                            resultsDiv.appendChild(div);
                        });
                    }
                    resultsDiv.classList.remove('hidden');
                });
            }, 300);
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            selectedId.value = '';
            selectedDiv.classList.add('hidden');
            searchContainer.classList.remove('hidden');
            searchInput.value = '';
        });
    }

    if (starBtns.length > 0 && ratingInput) {
        starBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;
                updateStars(rating);
            });
            btn.addEventListener('mouseenter', function() { updateStars(this.getAttribute('data-rating')); });
            btn.addEventListener('mouseleave', function() { updateStars(ratingInput.value || 0); });
        });
        function updateStars(value) {
            starBtns.forEach(btn => {
                const rating = btn.getAttribute('data-rating');
                if (rating <= value) {
                    btn.classList.remove('text-gray-300');
                    btn.classList.add('text-yellow-400');
                } else {
                    btn.classList.remove('text-yellow-400');
                    btn.classList.add('text-gray-300');
                }
            });
        }
    }
});
</script>