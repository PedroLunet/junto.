<!-- modal overlay -->
<div id="create-music-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">

            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Music Review</h3>
            </div>

            <!-- modal body -->
            <div class="p-8">
                <form id="create-music-review-form" action="{{ route('reviews.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="music">

                    <!-- music search section -->
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">What song did you listen to?</label>
                        <div class="relative" id="musicSearchContainer">
                            <input type="text" id="modalMusicSearch" placeholder="Search for a song..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
                                autocomplete="off">
                            <div id="modalMusicSearchResults"
                                class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1">
                            </div>
                        </div>

                        <!-- selected music preview -->
                        <div id="modalSelectedMusic"
                            class="hidden mt-4 p-4 border rounded-lg bg-gray-50 flex items-start gap-4 relative">
                            <input type="hidden" name="spotify_id" id="selectedMusicId">

                            <img id="selectedMusicCover" src="" alt="Cover"
                                class="h-80 object-cover rounded shadow-sm">
                            <div>
                                <h4 id="selectedMusicTitle" class="text-4xl font-bold text-gray-800"></h4>
                                <p id="selectedMusicArtist" class="text-gray-600 text-lg"></p>
                                <p id="selectedMusicYear" class="text-gray-500"></p>
                            </div>
                            <button type="button" id="removeMusicBtn"
                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="music-star-rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    class="music-star-btn bg-transparent border-none p-0 h-auto leading-none shadow-none text-3xl text-gray-300 focus:text-gray-300 hover:text-yellow-400 hover:bg-transparent focus:bg-transparent transition-colors focus:outline-none"
                                    data-rating="{{ $i }}">
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="music-rating-input" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">Write your review...</label>
                        <textarea name="content"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]"
                            rows="4" placeholder="Share your thoughts!"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-ui.button type="button" id="cancel-music-review-button"
                            variant="secondary">Cancel</x-button>
                            <x-ui.button type="submit">Post</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const createButton = document.getElementById('music-button');
        const modal = document.getElementById('create-music-review-modal');
        const cancelButton = document.getElementById('cancel-music-review-button');
        const textarea = document.querySelector('#create-music-review-modal textarea');
        const form = document.getElementById('create-music-review-form');

        // Search elements
        const searchInput = document.getElementById('modalMusicSearch');
        const resultsDiv = document.getElementById('modalMusicSearchResults');
        const selectedMusicDiv = document.getElementById('modalSelectedMusic');
        const searchContainer = document.getElementById('musicSearchContainer');
        const removeMusicBtn = document.getElementById('removeMusicBtn');

        // Selected music inputs
        const selectedMusicId = document.getElementById('selectedMusicId');
        const selectedMusicTitle = document.getElementById('selectedMusicTitle');
        const selectedMusicYear = document.getElementById('selectedMusicYear');
        const selectedMusicArtist = document.getElementById('selectedMusicArtist');
        const selectedMusicCover = document.getElementById('selectedMusicCover');

        // Rating elements
        const starButtons = document.querySelectorAll('.music-star-btn');
        const ratingInput = document.getElementById('music-rating-input');

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
                    fetch(`/music/search?q=${encodeURIComponent(query)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(songs => {
                            displayMusicResults(songs.slice(0, 5));
                        })
                        .catch(err => console.error(err));
                }, 300);
            });
        }

        function displayMusicResults(songs) {
            if (songs.length === 0) {
                resultsDiv.classList.add('hidden');
                return;
            }

            resultsDiv.innerHTML = songs.map(song => `
                <div class="p-3 hover:bg-gray-100 cursor-pointer border-b flex items-center transition-colors" 
                     onclick="selectModalMusic('${song.id}', '${song.title.replace(/'/g, "\\'")}', '${song.creator.replace(/'/g, "\\'")}', '${song.coverimage || ''}', '${song.releaseyear || ''}')">
                    ${song.coverimage ? 
                        `<img src="${song.coverimage}" class="w-10 h-14 object-cover rounded mr-3" onerror="this.style.display='none'">` 
                        : 
                        `<div class="w-10 h-14 bg-gray-200 rounded mr-3 flex items-center justify-center text-xs text-gray-500">No Image</div>`
                    }
                    <div>
                        <div class="font-medium text-gray-800">${song.title}</div>
                        <div class="text-xs text-gray-500">${song.creator} • ${song.releaseyear || 'N/A'}</div>
                    </div>
                </div>
            `).join('');

            resultsDiv.classList.remove('hidden');
        }


        window.selectModalMusic = function(id, title, artist, cover, year) {
            // Set hidden inputs
            selectedMusicId.value = id;

            // Update preview
            selectedMusicTitle.textContent = title;
            selectedMusicArtist.textContent = artist;
            selectedMusicYear.textContent = year || 'N/A';

            if (cover) {
                selectedMusicCover.src = cover;
                selectedMusicCover.classList.remove('hidden');
            } else {
                selectedMusicCover.classList.add('hidden');
            }

            // Show selection, hide search
            searchContainer.classList.add('hidden');
            selectedMusicDiv.classList.remove('hidden');
            resultsDiv.classList.add('hidden');
            searchInput.value = '';
        };

        if (removeMusicBtn) {
            removeMusicBtn.addEventListener('click', function() {
                selectedMusicId.value = '';
                selectedMusicDiv.classList.add('hidden');
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
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                modal.style.display = 'none';
                modal.classList.add('hidden');
                if (textarea) {
                    textarea.value = '';
                }
                if (ratingInput) {
                    ratingInput.value = '';
                    updateStars(0);
                }

                // Reset music selection
                selectedMusicId.value = '';

                selectedMusicDiv.classList.add('hidden');
                searchContainer.classList.remove('hidden');
                searchInput.value = '';
            });
        }

        // Close modal if clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        });

        // handle form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                // Validate required fields
                if (!formData.get('spotify_id')) {
                    alert('Please select a song');
                    return;
                }
                if (!formData.get('rating')) {
                    alert('Please select a rating');
                    return;
                }

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // close modal and reset form
                            modal.style.display = 'none';
                            modal.classList.add('hidden');
                            form.reset();

                            selectedMusicId.value = '';
                            selectedMusicDiv.classList.add('hidden');
                            searchContainer.classList.remove('hidden');
                            ratingInput.value = '';
                            updateStars(0);

                            // reload page to show new review
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error creating review');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while posting the review');
                    });
            });
        }
    });
</script>
