
<!-- modal overlay -->
<div id="create-movie-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">

            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Movie Review</h3>
            </div>
            
            <!-- modal body -->
            <div class="p-8">
                <form id="create-movie-review-form" method="POST">
                    @csrf
                    
                    <!-- Movie Search Section -->
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">What movie did you watch?</label>
                        <div class="relative" id="searchContainer">
                            <input 
                                type="text" 
                                id="modalMovieSearch" 
                                placeholder="Search for a movie..." 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
                                autocomplete="off"
                            >
                            <div id="modalSearchResults" class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1"></div>
                        </div>
                        
                        <!-- Selected Movie Preview -->
                        <div id="modalSelectedMovie" class="hidden mt-4 p-4 border rounded-lg bg-gray-50 items-start gap-4 relative">
                            <input type="hidden" name="tmdb_id" id="selectedMovieId">
                            <img id="selectedMoviePoster" src="" alt="Poster" class="w-auto h-92 object-cover rounded shadow-sm">
                            <div>
                                <h4 id="selectedMovieTitle" class="font-bold text-4xl"></h4>
                                <p id="selectedMovieYear" class="text-gray-600"></p>
                            </div>
                            <button type="button" id="removeMovieBtn" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="star-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="star-btn bg-transparent border-none p-0 h-auto leading-none shadow-none text-3xl text-gray-300 hover:text-yellow-400 hover:bg-transparent focus:bg-transparent transition-colors focus:outline-none" data-rating="{{ $i }}"> <!-- tirar depois que mudarmos o miligram -->
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating-input" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">Write your review...</label>
                        <textarea name="content" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]" rows="4" placeholder="Share your thoughts!"></textarea>
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
        
        // Search elements
        const searchInput = document.getElementById('modalMovieSearch');
        const resultsDiv = document.getElementById('modalSearchResults');
        const selectedMovieDiv = document.getElementById('modalSelectedMovie');
        const searchContainer = document.getElementById('searchContainer');
        const removeMovieBtn = document.getElementById('removeMovieBtn');
        
        // Selected movie inputs
        const selectedMovieId = document.getElementById('selectedMovieId');
        const selectedMovieTitle = document.getElementById('selectedMovieTitle');
        const selectedMovieYear = document.getElementById('selectedMovieYear');
        const selectedMoviePoster = document.getElementById('selectedMoviePoster');

        // Rating elements
        const starButtons = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('rating-input');

        // Rating logic
        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const rating = this.dataset.rating;
                ratingInput.value = rating;
                updateStars(rating);
            });

            button.addEventListener('mouseenter', function() {
                updateStars(this.dataset.rating);
            });

            button.addEventListener('mouseleave', function() {
                updateStars(ratingInput.value || 0);
            });
        });

        function updateStars(rating) {
            starButtons.forEach(btn => {
                const star = btn.querySelector('i');
                const btnRating = parseInt(btn.dataset.rating);
                const currentRating = parseInt(rating);
                
                if (btnRating <= currentRating) {
                    btn.classList.remove('text-gray-300');
                    btn.classList.add('text-yellow-400');
                    star.classList.remove('fa-regular');
                    star.classList.add('fa-solid');
                } else {
                    btn.classList.add('text-gray-300');
                    btn.classList.remove('text-yellow-400');
                    star.classList.add('fa-regular');
                    star.classList.remove('fa-solid');
                }
            });
        }

        let timeoutId;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    resultsDiv.classList.add('hidden');
                    return;
                }
                
                timeoutId = setTimeout(() => {
                    fetch(`/movies/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(movies => {
                            displayResults(movies.slice(0, 5));
                        })
                        .catch(err => console.error(err));
                }, 300);
            });
        }

        function displayResults(movies) {
            if (movies.length === 0) {
                resultsDiv.classList.add('hidden');
                return;
            }
            
            resultsDiv.innerHTML = movies.map(movie => `
                <div class="p-3 hover:bg-gray-100 cursor-pointer border-b flex items-center transition-colors" 
                     onclick="selectModalMovie(${movie.id}, '${movie.title.replace(/'/g, "\\'")}', '${movie.poster_path || ''}', '${movie.release_date || ''}')">
                    ${movie.poster_path ? 
                        `<img src="https://image.tmdb.org/t/p/w92${movie.poster_path}" class="w-10 h-14 object-cover rounded mr-3" onerror="this.style.display='none'">` 
                        : 
                        `<div class="w-10 h-14 bg-gray-200 rounded mr-3 flex items-center justify-center text-xs text-gray-500">No Image</div>`
                    }
                    <div>
                        <div class="font-medium text-gray-800">${movie.title}</div>
                        <div class="text-xs text-gray-500">${movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A'}</div>
                    </div>
                </div>
            `).join('');
            
            resultsDiv.classList.remove('hidden');
        }

        
        window.selectModalMovie = function(id, title, posterPath, releaseDate) {
            // Set hidden input
            selectedMovieId.value = id;
            
            // Update preview
            selectedMovieTitle.textContent = title;
            selectedMovieYear.textContent = releaseDate ? new Date(releaseDate).getFullYear() : 'N/A';
            
            if (posterPath) {
                selectedMoviePoster.src = `https://image.tmdb.org/t/p/w92${posterPath}`;
                selectedMoviePoster.classList.remove('hidden');
            } else {
                selectedMoviePoster.classList.add('hidden');
            }
            
            // Show selection, hide search
            searchContainer.classList.add('hidden');
            selectedMovieDiv.classList.remove('hidden');
            resultsDiv.classList.add('hidden');
            searchInput.value = '';
        };

        if (removeMovieBtn) {
            removeMovieBtn.addEventListener('click', function() {
                selectedMovieId.value = '';
                selectedMovieDiv.classList.add('hidden');
                searchContainer.classList.remove('hidden');
                searchInput.focus();
            });
        }

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (searchInput && !searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.classList.add('hidden');
            }
        });
                
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
