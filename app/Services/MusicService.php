<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MusicService
{
    protected $clientId;
    protected $clientSecret;
    protected $authUrl;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
        $this->authUrl = 'https://accounts.spotify.com/api/token';
        $this->baseUrl = 'https://api.spotify.com/v1';
    }

    protected function getAccessToken()
    {
        return Cache::remember('spotify_access_token', 3500, function () {
            $response = Http::asForm()->post($this->authUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            return null;
        });
    }

    public function searchTracks($query)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['error' => 'Unable to authenticate with Spotify'];
        }

        $response = Http::withToken($token)->get("{$this->baseUrl}/search", [
            'q' => $query,
            'type' => 'track',
            'limit' => 10
        ]);

        return $response->json();
    }

    public function getTrack($id)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['error' => 'Unable to authenticate with Spotify'];
        }

        $response = Http::withToken($token)->get("{$this->baseUrl}/tracks/{$id}");

        return $response->json();
    }
}
