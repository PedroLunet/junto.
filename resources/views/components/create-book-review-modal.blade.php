
<!-- modal overlay -->
<div id="create-book-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">

            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Book Review</h3>
            </div>
            
            <!-- modal body -->
            <div class="p-8">
                <form id="create-book-review-form" action="{{ route('reviews.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="book">
                    
                    <!-- Book Search Section -->
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">What book did you read?</label>
                        <div class="relative" id="bookSearchContainer">
                            <input 
                                type="text" 
                                id="modalBookSearch" 
                                placeholder="Search for a book..." 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
                                autocomplete="off"
                            >
                            <div id="modalBookSearchResults" class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1"></div>
                        </div>
                        
                        <!-- Selected Book Preview -->
                        <div id="modalSelectedBook" class="hidden mt-4 p-4 border rounded-lg bg-gray-50 flex items-start gap-4 relative">
                            <input type="hidden" name="google_book_id" id="selectedBookId">
                            <input type="hidden" name="title" id="hiddenBookTitle">
                            <input type="hidden" name="creator" id="hiddenBookAuthor">
                            <input type="hidden" name="release_year" id="hiddenBookYear">
                            <input type="hidden" name="cover_image" id="hiddenBookCover">

                            <img id="selectedBookCover" src="" alt="Cover" class="h-80 object-cover rounded shadow-sm">
                            <div>
                                <h4 id="selectedBookTitle" class="text-4xl font-bold text-gray-800"></h4>
                                <p id="selectedBookAuthor" class="text-gray-600 text-lg"></p>
                                <p id="selectedBookYear" class="text-gray-500"></p>
                            </div>
                            <button type="button" id="removeBookBtn" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="book-star-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="book-star-btn bg-transparent border-none p-0 h-auto leading-none shadow-none text-3xl text-gray-300 focus:text-gray-300 hover:text-yellow-400 hover:bg-transparent focus:bg-transparent transition-colors focus:outline-none" data-rating="{{ $i }}">
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="book-rating-input" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">Write your review...</label>
                        <textarea name="content" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#38157a]" rows="4" placeholder="Share your thoughts!"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-book-review-button" class="px-4 py-2 text-gray-800 border border-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-[#38157a] text-white rounded hover:bg-[#7455ad]">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function (){
        const createButton = document.getElementById('book-button');
        const modal = document.getElementById('create-book-review-modal');
        const cancelButton = document.getElementById('cancel-book-review-button');
        const textarea = document.querySelector('#create-book-review-modal textarea');
        const form = document.getElementById('create-book-review-form');
        
        // Search elements
        const searchInput = document.getElementById('modalBookSearch');
        const resultsDiv = document.getElementById('modalBookSearchResults');
        const selectedBookDiv = document.getElementById('modalSelectedBook');
        const searchContainer = document.getElementById('bookSearchContainer');
        const removeBookBtn = document.getElementById('removeBookBtn');
        
        // Selected book inputs
        const selectedBookId = document.getElementById('selectedBookId');
        const selectedBookTitle = document.getElementById('selectedBookTitle');
        const selectedBookYear = document.getElementById('selectedBookYear');
        const selectedBookAuthor = document.getElementById('selectedBookAuthor');
        const selectedBookCover = document.getElementById('selectedBookCover');

        // Hidden inputs for backend
        const hiddenBookTitle = document.getElementById('hiddenBookTitle');
        const hiddenBookAuthor = document.getElementById('hiddenBookAuthor');
        const hiddenBookYear = document.getElementById('hiddenBookYear');
        const hiddenBookCover = document.getElementById('hiddenBookCover');

        // Rating elements
        const starButtons = document.querySelectorAll('.book-star-btn');
        const ratingInput = document.getElementById('book-rating-input');

        // Rating logic
        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const rating = this.dataset.rating;
                ratingInput.value = rating;
                updateBookStars(rating);
            });

            button.addEventListener('mouseenter', function() {
                updateBookStars(this.dataset.rating);
            });

            button.addEventListener('mouseleave', function() {
                updateBookStars(ratingInput.value || 0);
            });
        });

        function updateBookStars(rating) {
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
                    fetch(`/books?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(books => {
                            displayBookResults(books.slice(0, 5));
                        })
                        .catch(err => console.error(err));
                }, 300);
            });
        }

        function displayBookResults(books) {
            if (books.length === 0) {
                resultsDiv.classList.add('hidden');
                return;
            }
            
            resultsDiv.innerHTML = books.map(book => `
                <div class="p-3 hover:bg-gray-100 cursor-pointer border-b flex items-center transition-colors" 
                     onclick="selectModalBook('${book.id}', '${book.title.replace(/'/g, "\\'")}', '${book.creator.replace(/'/g, "\\'")}', '${book.coverimage || ''}', '${book.releaseyear || ''}')">
                    ${book.coverimage ? 
                        `<img src="${book.coverimage}" class="w-10 h-14 object-cover rounded mr-3" onerror="this.style.display='none'">` 
                        : 
                        `<div class="w-10 h-14 bg-gray-200 rounded mr-3 flex items-center justify-center text-xs text-gray-500">No Image</div>`
                    }
                    <div>
                        <div class="font-medium text-gray-800">${book.title}</div>
                        <div class="text-xs text-gray-500">${book.creator} • ${book.releaseyear || 'N/A'}</div>
                    </div>
                </div>
            `).join('');
            
            resultsDiv.classList.remove('hidden');
        }

        
        window.selectModalBook = function(id, title, author, cover, year) {
            // Set hidden inputs
            selectedBookId.value = id;
            hiddenBookTitle.value = title;
            hiddenBookAuthor.value = author;
            hiddenBookYear.value = year;
            hiddenBookCover.value = cover;
            
            // Update preview
            selectedBookTitle.textContent = title;
            selectedBookAuthor.textContent = author;
            selectedBookYear.textContent = year || 'N/A';

            if (cover) {
                selectedBookCover.src = cover;
                selectedBookCover.classList.remove('hidden');
            } else {
                selectedBookCover.classList.add('hidden');
            }
            
            // Show selection, hide search
            searchContainer.classList.add('hidden');
            selectedBookDiv.classList.remove('hidden');
            resultsDiv.classList.add('hidden');
            searchInput.value = '';
        };

        if (removeBookBtn) {
            removeBookBtn.addEventListener('click', function() {
                selectedBookId.value = '';
                selectedBookDiv.classList.add('hidden');
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
                if (ratingInput) {
                    ratingInput.value = '';
                    updateBookStars(0);
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

        // handle form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // Validate required fields
                if (!formData.get('google_book_id')) {
                    alert('Please select a book');
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
                        
                        // eeset custom elements
                        selectedBookId.value = '';
                        selectedBookDiv.classList.add('hidden');
                        searchContainer.classList.remove('hidden');
                        ratingInput.value = '';
                        updateBookStars(0);
                        
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
