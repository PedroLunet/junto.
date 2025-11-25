<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MusicService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
        $this->baseUrl = 'https://api.spotify.com/v1';
    }

    // get Spotify access token for API requests
    private function getAccessToken(): ?string
    {
        if (!$this->clientId || !$this->clientSecret) {
            return null;
        }

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $response = Http::withHeaders($headers)->asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'client_credentials',
        ]);

        $result = $response->json();
        return $result['access_token'] ?? null;
    }

    // search for tracks on Spotify
    public function searchTracks($query, $limit = 10)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['tracks' => ['items' => []]];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get("{$this->baseUrl}/search", [
            'q' => $query,
            'type' => 'track',
            'limit' => $limit
        ]);

        return $response->json();
    }

    // get a specific track by ID
    public function getTrack($id)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get("{$this->baseUrl}/tracks/{$id}");

        return $response->json();
    }

    // format raw Spotify API data into standardized format
    public function formatMusicData(array $spotifyTracks): array
    {
        $formattedTracks = [];

        foreach ($spotifyTracks as $track) {
            $formattedTracks[] = [
                'id' => $track['id'] ?? null,
                'title' => $track['name'] ?? 'Unknown Title',
                'creator' => isset($track['artists'][0]['name']) ? $track['artists'][0]['name'] : 'Unknown Artist',
                'releaseYear' => isset($track['album']['release_date']) ? substr($track['album']['release_date'], 0, 4) : null,
                'coverImage' => isset($track['album']['images'][0]['url']) ? $track['album']['images'][0]['url'] : null,
                'releaseyear' => isset($track['album']['release_date']) ? substr($track['album']['release_date'], 0, 4) : null,
                'coverimage' => isset($track['album']['images'][0]['url']) ? $track['album']['images'][0]['url'] : null,
                'type' => 'music'
            ];
        }

        return $formattedTracks;
    }
}
