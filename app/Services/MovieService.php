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
            'api_key' => $this->apiKey,
            'append_to_response' => 'credits'
        ]);

        return $response->json();
    }

    // get movie details with credits included
    public function getMovieWithCredits($movieId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/{$movieId}", [
                'api_key' => $this->apiKey,
                'append_to_response' => 'credits'
            ]);

            if ($response->successful()) {
                $movieData = $response->json();
                $crew = $movieData['credits']['crew'] ?? [];

                foreach ($crew as $member) {
                    if ($member['job'] === 'Director') {
                        return $member['name'];
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return 'Unknown Director';
    }

    // format raw TMDB API data into standardized format
    public function formatMovieData(array $tmdbMovies, $includeDirectors = false): array
    {
        $formattedMovies = [];

        foreach ($tmdbMovies as $movie) {
            $movieData = [
                'id' => $movie['id'] ?? null,
                'title' => $movie['title'] ?? 'Unknown Title',
                'releaseYear' => isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : null,
                'coverImage' => isset($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $movie['poster_path'] : null,
                'poster_path' => $movie['poster_path'] ?? null,
                'release_date' => $movie['release_date'] ?? null,
                'type' => 'movie'
            ];

            // only include creator/director if explicitly requested
            if ($includeDirectors && isset($movie['id'])) {
                $director = $this->getMovieWithCredits($movie['id']);
                $movieData['creator'] = $director;
            }

            $formattedMovies[] = $movieData;
        }

        return $formattedMovies;
    }
}
