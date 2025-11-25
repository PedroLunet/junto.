<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MovieService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->baseUrl = config('services.tmdb.base_url');
    }

    public function searchMovies($query, $page = 1)
    {
        $response = Http::get("{$this->baseUrl}/search/movie", [
            'api_key' => $this->apiKey,
            'query' => $query,
            'page' => $page
        ]);

        return $response->json();
    }

    public function getMovie($id)
    {
        $response = Http::get("{$this->baseUrl}/movie/{$id}", [
            'api_key' => $this->apiKey
        ]);

        return $response->json();
    }

    // Format raw TMDB API data into standardized format
    public function formatMovieData(array $tmdbMovies): array
    {
        $formattedMovies = [];

        foreach ($tmdbMovies as $movie) {
            $formattedMovies[] = [
                'id' => $movie['id'] ?? null,
                'title' => $movie['title'] ?? 'Unknown Title',
                'creator' => 'Unknown Director', // TMDB basic search doesn't include director info
                'releaseYear' => isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : null,
                'coverImage' => isset($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $movie['poster_path'] : null,
                'poster_path' => $movie['poster_path'] ?? null,
                'release_date' => $movie['release_date'] ?? null,
                'type' => 'movie'
            ];
        }

        return $formattedMovies;
    }
}
