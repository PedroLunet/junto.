<div id="create-group-music-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Group Music Review</h3>
            </div>
            <div class="p-8">
                <form id="create-group-music-review-form" action="{{ route('groups.reviews.store', ['group' => $group->id, 'type' => 'music']) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="music">
                    <input type="hidden" name="group_id" value="{{ $group->id }}">

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">What music did you listen to?</label>
                        <div class="relative" id="groupMusicSearchContainer">
                            <input type="text" id="groupModalMusicSearch" placeholder="Search for an album or song..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
                                autocomplete="off">
                            <div id="groupModalMusicSearchResults"
                                class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1">
                            </div>
                        </div>
                        
                        <div id="groupModalSelectedMusic"
                            class="hidden mt-4 p-4 border rounded-lg bg-gray-50 flex items-start gap-4 relative">
                            <input type="hidden" name="spotify_id" id="groupSelectedMusicId">
                            <img id="groupSelectedMusicCover" src="" alt="Cover"
                                class="h-20 w-20 object-cover rounded shadow-sm">
                            <div>
                                <h4 id="groupSelectedMusicTitle" class="text-xl font-bold text-gray-800"></h4>
                                <p id="groupSelectedMusicArtist" class="text-gray-600"></p>
                                <p id="groupSelectedMusicYear" class="text-gray-500 text-sm"></p>
                            </div>
                            <button type="button" id="groupRemoveMusicBtn"
                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="group-music-star-rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    class="group-music-star-btn text-3xl text-gray-300 hover:text-yellow-400 focus:outline-none transition-colors"
                                    data-rating="{{ $i }}">
                                    <i class="fas fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="group-music-rating-input" required>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Your Review</label>
                        <textarea name="content" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a]" rows="5" placeholder="Share your thoughts..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-ui.button type="button" id="cancel-group-music-review-button" variant="secondary">Cancel</x-ui.button>
                        <x-ui.button type="submit">Post Review</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const musicForm = document.getElementById('create-group-music-review-form');
    const musicModal = document.getElementById('create-group-music-review-modal');
    if (musicForm && musicModal) {
        musicForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(musicForm);
            fetch(musicForm.action, {
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
                    musicModal.classList.add('hidden');
                    musicModal.style.display = 'none';
                    musicForm.reset();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error creating review');
                }
            })
            .catch(() => { alert('An error occurred while posting the review'); });
        });
    }
    const openBtn = document.getElementById('group-music-review-button');
    const modal = document.getElementById('create-group-music-review-modal');
    const cancelBtn = document.getElementById('cancel-group-music-review-button');
    const starBtns = document.querySelectorAll('.group-music-star-btn');
    const ratingInput = document.getElementById('group-music-rating-input');

    const searchInput = document.getElementById('groupModalMusicSearch');
    const resultsDiv = document.getElementById('groupModalMusicSearchResults');
    const searchContainer = document.getElementById('groupMusicSearchContainer');
    const selectedDiv = document.getElementById('groupModalSelectedMusic');
    const selectedId = document.getElementById('groupSelectedMusicId');
    const selectedTitle = document.getElementById('groupSelectedMusicTitle');
    const selectedArtist = document.getElementById('groupSelectedMusicArtist');
    const selectedYear = document.getElementById('groupSelectedMusicYear');
    const selectedCover = document.getElementById('groupSelectedMusicCover');
    const removeBtn = document.getElementById('groupRemoveMusicBtn');

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
                fetch(`/music/search?q=${encodeURIComponent(query)}`, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(songs => {
                    resultsDiv.innerHTML = '';
                    if(songs.length === 0) {
                         resultsDiv.innerHTML = '<div class="p-3 text-gray-500">No music found</div>';
                    } else {
                        songs.slice(0, 5).forEach(song => {
                            const div = document.createElement('div');
                            div.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b flex items-center transition-colors';
                            
                            div.innerHTML = `
                                <div class="flex items-center w-full">
                                    ${song.coverimage ? `<img src="${song.coverimage}" class="w-10 h-10 object-cover rounded mr-3">` : `<div class="w-10 h-10 bg-gray-200 rounded mr-3"></div>`}
                                    <div><div class="font-medium">${song.title}</div><div class="text-xs text-gray-500">${song.creator}</div></div>
                                </div>`;
                            
                            div.addEventListener('click', () => {
                                selectedId.value = song.id;
                                selectedTitle.textContent = song.title;
                                selectedArtist.textContent = song.creator;
                                selectedYear.textContent = song.releaseyear || '';
                                selectedCover.src = song.coverimage || '';
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

    if(removeBtn) {
        removeBtn.addEventListener('click', () => {
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