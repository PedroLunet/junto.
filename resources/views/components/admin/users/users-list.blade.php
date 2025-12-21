<div id="user-cards-list" class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
    @forelse($users as $user)
        <x-admin.users.user-card :user="$user" class="user-card w-full" />
    @empty
        <x-ui.empty-state icon="fa-users" title="No users found" description="There are no users to display."
            height="min-h-[200px]" class="col-span-full" />
    @endforelse
</div>

<script>
    // only run this script if the search bar exists (mobile card view)
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchUserList');
        const cardContainer = document.getElementById('user-cards-list');
        if (!searchInput || !cardContainer) return;
        let sortOrder = 'asc';
        let sortBy = 'name';

        // Store all user cards initially
        const allCards = Array.from(cardContainer.querySelectorAll('.user-card'));

        function getCardData(card) {
            const name = card.querySelector('h3')?.textContent.trim() || '';
            const username = card.querySelector('[data-user-username]')?.getAttribute('data-user-username') ||
                card.querySelectorAll('span.text-gray-900')[0]?.textContent.trim() || '';
            const email = card.querySelector('[data-user-email]')?.getAttribute('data-user-email') || card
                .querySelectorAll('span.text-gray-900')[1]?.textContent.trim() || '';
            const joined = card.querySelectorAll('span.text-gray-900')[2]?.textContent.trim() || '';
            return {
                name,
                username,
                email,
                joined,
                card
            };
        }

        function sortAndRenderCards(filteredCards) {
            // Sort filtered cards and re-append them
            const cardData = filteredCards.map(getCardData);
            cardData.sort((a, b) => {
                let valA, valB;
                switch (sortBy) {
                    case 'name':
                        valA = a.name.toLowerCase();
                        valB = b.name.toLowerCase();
                        break;
                    case 'username':
                        valA = a.username.toLowerCase();
                        valB = b.username.toLowerCase();
                        break;
                    case 'email':
                        valA = a.email.toLowerCase();
                        valB = b.email.toLowerCase();
                        break;
                    case 'date':
                        valA = Date.parse(a.joined) || a.joined;
                        valB = Date.parse(b.joined) || b.joined;
                        break;
                    default:
                        valA = a.name.toLowerCase();
                        valB = b.name.toLowerCase();
                }
                if (valA < valB) return sortOrder === 'asc' ? -1 : 1;
                if (valA > valB) return sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
            // Remove all cards from container
            allCards.forEach(card => {
                if (card.parentNode === cardContainer) {
                    cardContainer.removeChild(card);
                }
            });
            // Re-append sorted, filtered cards
            cardData.forEach(data => cardContainer.appendChild(data.card));
        }

        function filterCards() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            // Remove any no-results div
            let noResultsDiv = document.getElementById('no-results-card-list');
            if (noResultsDiv && noResultsDiv.parentNode === cardContainer) {
                cardContainer.removeChild(noResultsDiv);
            }
            // Remove any empty state (if present)
            const emptyState = cardContainer.querySelector(
                'x-ui-empty-state, .empty-state, [data-empty-state]');
            if (emptyState && emptyState.parentNode === cardContainer) {
                cardContainer.removeChild(emptyState);
            }
            // Filter cards to match search
            const filteredCards = allCards.filter(card => {
                const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const username = card.querySelector('[data-user-username]')?.getAttribute(
                    'data-user-username')?.toLowerCase() || card.querySelectorAll(
                    'span.text-gray-900')[0]?.textContent.toLowerCase() || '';
                const email = card.querySelector('[data-user-email]')?.getAttribute('data-user-email')
                    ?.toLowerCase() || card.querySelectorAll('span.text-gray-900')[1]?.textContent
                    .toLowerCase() || '';
                return (
                    name.includes(searchTerm) ||
                    username.includes(searchTerm) ||
                    email.includes(searchTerm)
                );
            });
            sortAndRenderCards(filteredCards);
            if (filteredCards.length === 0) {
                if (searchTerm !== '') {
                    // Show no results div
                    if (!noResultsDiv) {
                        noResultsDiv = document.createElement('div');
                        noResultsDiv.id = 'no-results-card-list';
                        noResultsDiv.className =
                            'col-span-full py-10 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300 mt-4';
                    }
                    noResultsDiv.textContent = `No users found matching "${searchTerm}"`;
                    cardContainer.appendChild(noResultsDiv);
                } else {
                    // Show empty state if it exists in the DOM (Blade renders it if there are no users)
                    if (emptyState) cardContainer.appendChild(emptyState);
                }
            }
        }

        window.sortUserCards = function(sortKey) {
            sortBy = sortKey;
            filterCards(); // filterCards will call sortAndRenderCards internally
        };
        window.toggleUserCardsOrder = function() {
            sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            const icon = document.getElementById('sort-order-icon');
            if (icon) {
                icon.classList.toggle('fa-arrow-down', sortOrder === 'asc');
                icon.classList.toggle('fa-arrow-up', sortOrder === 'desc');
            }
            filterCards();
        };

        // Defer initial filter and sort until after rendering to avoid grid flicker
        requestAnimationFrame(() => {
            filterCards();
        });

        searchInput.addEventListener('input', filterCards);
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterCards();
            }
        });
    });
</script>
