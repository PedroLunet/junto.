<div id="addFavModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4">
        <!-- header -->
        <div class="flex items-center justify-between p-8">
            <h2 id="modalTitle" class="text-3xl font-bold text-gray-900">Add favorite</h2>
            <x-ui.icon-button id="closeModal" variant="gray" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fa-solid fa-times text-xl"></i>
            </x-ui.icon-button>
        </div>

        <!-- body -->
        <div class="flex-1 p-8">
            <x-ui.media-search searchId="favSearch"
                searchResultsId="favSearchResults" searchPlaceholder="Search..." label="Search for your favorite" />
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

        function displaySearchResults(results) {
            if (results.length === 0) {
                searchResultsDiv.classList.add('hidden');
                selectedIndex = -1;
                searchResults = [];
                return;
            }

            searchResults = results;
            selectedIndex = -1;

            searchResultsDiv.innerHTML = results.map((item, index) => {
                const imageUrl = item.coverImage || null;
                const title = item.title || 'Unknown Title';

                // show different subtitle based on type
                let subtitle;
                if (currentType === 'movie') {
                    subtitle = item.releaseYear ? `${item.releaseYear}` : 'Unknown Year';
                } else {
                    subtitle = item.creator || 'Unknown Creator';
                }

                const selectFunction = `selectItem(${index})`;

                return `
                    <div class="search-result-item p-4 hover:bg-gray-100 cursor-pointer border-b flex items-center" onclick="${selectFunction}" data-index="${index}">
                        ${imageUrl ? 
                            `<img src="${imageUrl}" class="w-12 h-18 object-cover rounded mr-3" alt="${title.replace(/"/g, '&quot;')}" onerror="this.style.display='none'">` 
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
                    item.scrollIntoView({ // scroll item into view if needed
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
                selectItem(selectedIndex);
            }
        }

        window.selectItem = function(itemIndex) {
            if (itemIndex >= 0 && itemIndex < searchResults.length) {
                const item = searchResults[itemIndex];
                saveFavorite(item);
            }
        };

        function saveFavorite(item) {
            fetch('/profile/add-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: item.id || null,
                        title: item.title,
                        creator: item.creator,
                        releaseYear: item.releaseYear,
                        coverImage: item.coverImage,
                        type: currentType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModalHandler();
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
                return;
            }

            searchTimeoutId = setTimeout(() => {
                const searchQuery = currentQuery;
                let endpoint;

                switch (currentType) {
                    case 'movie':
                        endpoint = `/movies/search?q=${encodeURIComponent(query)}`;
                        break;
                    case 'book':
                        endpoint = `/books/search?q=${encodeURIComponent(query)}`;
                        break;
                    case 'music':
                        endpoint = `/music/search?q=${encodeURIComponent(query)}`;
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
                            displaySearchResults(results.slice(0, 5));
                        }
                    })
                    .catch(error => {
                        // only show error if this response is for the current query
                        if (searchQuery === currentQuery) {
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
