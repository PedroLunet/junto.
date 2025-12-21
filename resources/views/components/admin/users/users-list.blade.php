<div id="user-cards-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($users as $user)
        <div class="user-card">
            <x-admin.users.user-card :user="$user" />
        </div>
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

        function getCardData(card) {
            const name = card.querySelector('h3')?.textContent.trim() || '';
            // try to get username/email from data attributes if present, else fallback to text
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

        function sortCards() {
            // only sort visible cards
            const cards = Array.from(cardContainer.querySelectorAll('.user-card'));
            const visibleCards = cards.filter(card => card.style.display !== 'none');
            const cardData = visibleCards.map(getCardData);
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
            cardData.forEach(data => cardContainer.appendChild(data.card));
        }

        function filterCards() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const cards = cardContainer.querySelectorAll('.user-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const username = card.querySelector('[data-user-username]')?.getAttribute(
                    'data-user-username')?.toLowerCase() || card.querySelectorAll(
                    'span.text-gray-900')[0]?.textContent.toLowerCase() || '';
                const email = card.querySelector('[data-user-email]')?.getAttribute('data-user-email')
                    ?.toLowerCase() || card.querySelectorAll('span.text-gray-900')[1]?.textContent
                    .toLowerCase() || '';
                if (
                    name.includes(searchTerm) ||
                    username.includes(searchTerm) ||
                    email.includes(searchTerm)
                ) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            sortCards();
            const emptyState = cardContainer.querySelector(
                'x-ui-empty-state, .empty-state, [data-empty-state]');
            let noResultsDiv = document.getElementById('no-results-card-list');
            if (visibleCount === 0 && searchTerm !== '') {
                if (emptyState) emptyState.style.display = 'none';
                if (!noResultsDiv) {
                    noResultsDiv = document.createElement('div');
                    noResultsDiv.id = 'no-results-card-list';
                    noResultsDiv.className =
                        'col-span-full py-10 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300 mt-4';
                    cardContainer.appendChild(noResultsDiv);
                }
                noResultsDiv.textContent = `No users found matching "${searchTerm}"`;
                noResultsDiv.style.display = '';
            } else {
                if (noResultsDiv) noResultsDiv.style.display = 'none';
                if (emptyState) emptyState.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        window.sortUserCards = function(sortKey) {
            sortBy = sortKey;
            filterCards(); // filterCards will call sortCards internally
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

        filterCards(); // initial filter and sort

        searchInput.addEventListener('input', filterCards);
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterCards();
            }
        });
    });
</script>
