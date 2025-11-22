<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MovieService{
    protected $apiKey;
    protected $baseUrl;

    public function __construct(){
        $this->apiKey = config('services.tmdb.api_key');
        $this->baseUrl = config('services.tmdb.base_url');
    }

    public function searchMovies($query) {
        $response = Http::get("{$this->baseUrl}/search/movie", [
            'api_key' => $this->apiKey,
            'query' => $query,
            'page' => 1
        ]);

        return $response -> json();
    }

    public function getMovie($id){
        $response = Http::get("{$this->baseUrl}/movie/{$id}", [
            'api_key' => $this->apiKey
        ]);
        return $response->json();
    }
}