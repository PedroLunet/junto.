<!-- modal overlay -->
<div id="create-group-book-review-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-6xl w-full min-h-[400px]">
            <!-- modal header -->
            <div class="flex justify-between items-center p-8 border-b">
                <h3 class="text-4xl font-semibold">Create Group Book Review</h3>
            </div>
            <!-- modal body -->
            <div class="p-8">
                <form id="create-group-book-review-form" action="{{ route('reviews.store', ['type' => 'book', 'group' => $group->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="book">
                    <!-- Book Search Section (copy from main modal) -->
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">What book did you read?</label>
                        <div class="relative" id="groupBookSearchContainer">
                            <input type="text" id="groupModalBookSearch" placeholder="Search for a book..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
                                autocomplete="off">
                            <div id="groupModalBookSearchResults"
                                class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-60 overflow-y-auto z-20 mt-1">
                            </div>
                        </div>
                        <!-- Selected Book Preview -->
                        <div id="groupModalSelectedBook"
                            class="hidden mt-4 p-4 border rounded-lg bg-gray-50 flex items-start gap-4 relative">
                            <input type="hidden" name="google_books_id" id="groupSelectedBookId">
                            <img id="groupSelectedBookCover" src="" alt="Cover"
                                class="h-80 object-cover rounded shadow-sm">
                            <div>
                                <h4 id="groupSelectedBookTitle" class="text-4xl font-bold text-gray-800"></h4>
                                <p id="groupSelectedBookAuthor" class="text-gray-600"></p>
                                <p id="groupSelectedBookYear" class="text-gray-600 text-xl"></p>
                            </div>
                            <button type="button" id="groupRemoveBookBtn"
                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2" id="group-book-star-rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    class="group-book-star-btn bg-transparent border-none p-0 h-auto leading-none shadow-none text-3xl text-gray-300 focus:text-gray-300 hover:text-yellow-400 hover:bg-transparent focus:bg-transparent transition-colors focus:outline-none"
                                    data-rating="{{ $i }}">
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="group-book-rating-input" required>
                    </div>
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-2">Your Review</label>
                        <textarea name="content" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent" rows="5" placeholder="Share your thoughts..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <x-ui.button type="button" id="cancel-group-book-review-button" variant="secondary">Cancel</x-ui.button>
                        <x-ui.button type="submit">Post Review</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- JS for search, rating, modal open/close will be added to match main modal -->
