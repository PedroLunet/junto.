@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Movies</h1>
    
    <div class="mb-6">
        <div class="relative">
            <input 
                type="text" 
                id="movieSearch" 
                placeholder="Search for movies..." 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent"
            >
            <div id="searchResults" class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg hidden max-h-96 overflow-y-auto z-20 mt-1">

            </div>
        </div>
    </div>
    
    <div id="selectedMovies" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
   
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('movieSearch');
    const resultsDiv = document.getElementById('searchResults');
    const selectedMoviesDiv = document.getElementById('selectedMovies');
    
    let timeoutId;
    
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
                });
        }, 100);
    });
    
    function displayResults(movies) {
        if (movies.length === 0) {
            resultsDiv.classList.add('hidden');
            return;
        }
        
        resultsDiv.innerHTML = movies.map(movie => `
            <div class="p-4 hover:bg-gray-100 cursor-pointer border-b flex items-center" onclick="selectMovie(${movie.id}, '${movie.title.replace(/'/g, "\\'")}', '${movie.poster_path || ''}', '${movie.release_date || ''}')">
                ${movie.poster_path ? 
                    `<img src="https://image.tmdb.org/t/p/w92${movie.poster_path}" class="w-12 h-18 object-cover rounded mr-3" alt="${movie.title.replace(/"/g, '&quot;')}" onerror="this.style.display='none'">` 
                    : 
                    `<div class="w-12 h-18 bg-gray-200 rounded mr-3 flex items-center justify-center text-xs text-gray-500">No Image</div>`
                }
                <div>
                    <div class="font-medium">${movie.title}</div>
                    <div class="text-sm text-gray-600">${movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A'}</div>
                </div>
            </div>
        `).join('');
        
        resultsDiv.classList.remove('hidden');
    }
    
    window.selectMovie = function(id, title, posterPath, releaseDate) {
        searchInput.value = '';
        resultsDiv.classList.add('hidden');
        
        // add movie to selected movies
        const movieCard = document.createElement('div');
        movieCard.className = 'bg-white rounded-lg shadow-md overflow-hidden';
        movieCard.innerHTML = `
            ${posterPath ? 
                `<img src="https://image.tmdb.org/t/p/w300${posterPath}" class="w-full h-92 object-cover" alt="${title.replace(/"/g, '&quot;')}" onerror="this.style.display='none'">` 
                : 
                `<div class="w-full h-64 bg-gray-200 flex items-center justify-center text-gray-500">No Image Available</div>`
            }
            <div class="p-4">
                <h3 class="font-bold">${title}</h3>
                <p class="text-gray-600 text-lg">${releaseDate ? new Date(releaseDate).getFullYear() : 'N/A'}</p>
            </div>
        `;
        
        selectedMoviesDiv.appendChild(movieCard);
    }
    
    // hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.add('hidden');
        }
    });
});
</script>
@endsection