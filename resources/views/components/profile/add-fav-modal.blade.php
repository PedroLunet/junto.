<div id="addFavModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 id="modalTitle" class="text-3xl font-bold text-gray-900">Add favorite</h2>
            <a id="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </a>
        </div>

        <!-- body -->
        <div class="flex-1 p-8">
            <div class="relative">
                <input type="text" id="favSearch" placeholder="Search..."
                    class="w-full px-6 py-4 pr-16 text-xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                <div id="loadingSpinner" class="absolute right-4 top-1/2 transform -translate-y-1/2 hidden">
                    <svg class="animate-spin h-6 w-6 text-[#38157a]" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <div id="favSearchResults"
                    class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-96 overflow-y-auto z-30 mt-1">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        //=== MODAL ===
        const modal = document.getElementById('addFavModal');
        const modalTitle = document.getElementById('modalTitle');
        const closeModal = document.getElementById('closeModal');

        function closeModalHandler() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // close modal event listeners
        closeModal.addEventListener('click', closeModalHandler);

        // close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModalHandler();
            }
        });

        // close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModalHandler();
            }
        });

        //=== SEARCH ===
        let searchTimeoutId;
        let currentType = 'movie';
        let selectedIndex = -1;
        let searchResults = [];
        let currentQuery = '';
        const searchInput = document.getElementById('favSearch');
        const searchResultsDiv = document.getElementById('favSearchResults');
        const loadingSpinner = document.getElementById('loadingSpinner');

        function showLoading() {
            loadingSpinner.classList.remove('hidden');
        }

        function hideLoading() {
            loadingSpinner.classList.add('hidden');
        }

        window.openAddFavModal = function(type) {
            currentType = type;
            let title, placeholder;

            switch (type) {
                case 'book':
                    title = 'Add favorite book';
                    placeholder = 'Search for books...';
                    break;
                case 'movie':
                    title = 'Add favorite movie';
                    placeholder = 'Search for movies...';
                    break;
                case 'music':
                    title = 'Add favorite music';
                    placeholder = 'Search for music...';
                    break;
                default:
                    title = 'Add favorite';
                    placeholder = 'Search...';
            }

            modalTitle.textContent = title;
            searchInput.placeholder = placeholder;
            searchInput.value = '';
            searchResultsDiv.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // focus the input after a short delay (wait for modal to be fully rendered)
            setTimeout(() => {
                searchInput.focus();
            }, 100);
        };

        function displaySearchResults(results, type) {
            if (results.length === 0) {
                searchResultsDiv.classList.add('hidden');
                selectedIndex = -1;
                searchResults = [];
                return;
            }

            searchResults = results;
            selectedIndex = -1;

            searchResultsDiv.innerHTML = results.map((item, index) => {
                let imageUrl, title, subtitle, selectFunction;

                switch (type) {
                    case 'movie':
                        imageUrl = item.poster_path ?
                            `https://image.tmdb.org/t/p/w92${item.poster_path}` : null;
                        title = item.title;
                        subtitle = item.release_date ? new Date(item.release_date).getFullYear() :
                            'N/A';
                        const movieCoverImage = item.poster_path ?
                            `https://image.tmdb.org/t/p/w300${item.poster_path}` : null;
                        const movieYear = item.release_date ? item.release_date.substring(0, 4) : null;
                        const movieDirector = 'Unknown Director';
                        const movieTitle = item.title.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(
                            /\n/g, ' ').replace(/\r/g, ' ');
                        selectFunction =
                            `selectItem(null, '${movieTitle}', '${movieDirector}', '${movieYear}', '${movieCoverImage}')`;
                        break;
                    case 'book':
                        imageUrl = item.coverimage;
                        title = item.title;
                        subtitle = item.creator || 'Unknown Author';
                        selectFunction =
                            `selectItem(null, '${item.title.replace(/'/g, "\\'")}', '${item.creator.replace(/'/g, "\\'")}', '${item.releaseyear || ''}', '${item.coverimage || ''}')`;
                        break;
                    case 'music':
                        imageUrl = item.coverimage;
                        title = item.title;
                        subtitle = item.creator || 'Unknown Artist';
                        selectFunction =
                            `selectItem(null, '${item.title.replace(/'/g, "\\'")}', '${item.creator.replace(/'/g, "\\'")}', '${item.releaseyear || ''}', '${item.coverimage || ''}')`;
                        break;
                }

                return `
                    <div class="search-result-item p-4 hover:bg-gray-100 cursor-pointer border-b flex items-center" onclick="${selectFunction}" data-index="${index}">
                        ${imageUrl ? 
                            `<img src="${imageUrl}" class="w-12 h-18 object-cover rounded mr-3" onerror="this.style.display='none'">` 
                            : 
                            `<div class="w-12 h-18 bg-gray-200 rounded mr-3 flex items-center justify-center text-xs text-gray-500">No Image</div>`
                        }
                        <div>
                            <div class="font-medium">${title}</div>
                            <div class="text-sm text-gray-600">${subtitle}</div>
                        </div>
                    </div>
                `;
            }).join('');

            searchResultsDiv.classList.remove('hidden');
        }

        function updateSelectedItem() {
            const items = document.querySelectorAll('.search-result-item');
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('bg-[#38157a]', 'text-white');
                    item.classList.remove('hover:bg-gray-100');
                    // Scroll item into view if needed
                    item.scrollIntoView({
                        block: 'nearest'
                    });
                } else {
                    item.classList.remove('bg-[#38157a]', 'text-white');
                    item.classList.add('hover:bg-gray-100');
                }
            });
        }

        function selectCurrentItem() {
            if (selectedIndex >= 0 && selectedIndex < searchResults.length) {
                const item = searchResults[selectedIndex];
                let id, title, creator, releaseYear, coverImage;

                switch (currentType) {
                    case 'movie':
                        id = null;
                        title = item.title;
                        creator = 'Unknown Director';
                        releaseYear = item.release_date ? item.release_date.substring(0, 4) : null;
                        coverImage = item.poster_path ? `https://image.tmdb.org/t/p/w300${item.poster_path}` :
                            null;
                        break;
                    case 'book':
                        id = null;
                        title = item.title;
                        creator = item.creator;
                        releaseYear = item.releaseyear || '';
                        coverImage = item.coverimage || '';
                        break;
                    case 'music':
                        id = null;
                        title = item.title;
                        creator = item.creator;
                        releaseYear = item.releaseyear || '';
                        coverImage = item.coverimage || '';
                        break;
                }

                saveFavorite(id, title, creator, releaseYear, coverImage, currentType);
            }
        }

        window.selectItem = function(id, title, creator, releaseYear, coverImage) {
            saveFavorite(id, title, creator, releaseYear, coverImage, currentType);
        };

        function saveFavorite(id, title, creator, releaseYear, coverImage, type) {
            fetch('/profile/add-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        title: title,
                        creator: creator,
                        releaseYear: releaseYear,
                        coverImage: coverImage,
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModalHandler();
                        // reload the page to show the updated favorite
                        window.location.reload();
                    } else {
                        alert('Error saving favorite: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving favorite');
                });
        }

        // keyboard navigation for search results
        searchInput.addEventListener('keydown', function(e) {
            if (!searchResultsDiv.classList.contains('hidden') && searchResults.length > 0) {
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, searchResults.length - 1);
                        updateSelectedItem();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, 0);
                        updateSelectedItem();
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (selectedIndex >= 0) {
                            selectCurrentItem();
                        }
                        break;
                    case 'Escape':
                        searchResultsDiv.classList.add('hidden');
                        selectedIndex = -1;
                        break;
                }
            }
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeoutId);
            const query = this.value.trim();
            currentQuery = query;

            if (query.length < 2) {
                searchResultsDiv.classList.add('hidden');
                selectedIndex = -1;
                searchResults = [];
                currentQuery = '';
                hideLoading();
                return;
            }

            showLoading();
            searchTimeoutId = setTimeout(() => {
                const searchQuery = currentQuery;
                let endpoint;

                switch (currentType) {
                    case 'movie':
                        endpoint = `/movies/search?q=${encodeURIComponent(query)}`;
                        break;
                    case 'book':
                        endpoint = `/books?q=${encodeURIComponent(query)}`;
                        break;
                    case 'music':
                        endpoint = `/music?q=${encodeURIComponent(query)}`;
                        break;
                }

                fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(results => {
                        // only process results if this response is for the current query
                        if (searchQuery === currentQuery) {
                            hideLoading();
                            displaySearchResults(results.slice(0, 5), currentType);
                        }
                    })
                    .catch(error => {
                        // only show error if this response is for the current query
                        if (searchQuery === currentQuery) {
                            hideLoading();
                            console.error('Search error:', error);
                            searchResultsDiv.innerHTML =
                                `<div class="p-4 text-red-500">Error: ${error.message}</div>`;
                            searchResultsDiv.classList.remove('hidden');
                        }
                    });
            }, 100);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
                searchResultsDiv.classList.add('hidden');
            }
        })
    });
</script>
