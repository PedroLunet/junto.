<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Services\FavoriteService;

class MusicController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    private function getAccessToken()
    {
        $clientId = config('services.spotify.client_id');
        $clientSecret = config('services.spotify.client_secret');

        // Spotify requires the keys to be Base64 encoded for the token request
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $response = Http::withHeaders($headers)->asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'client_credentials',
        ]);

        return $response->json()['access_token'] ?? null;
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $query = $request->input('q');
        $rawSongs = [];

        if ($query) {
            // 1. get token
            $token = $this->getAccessToken();

            if ($token) {
                // 2. search API using the token
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])->get('https://api.spotify.com/v1/search', [
                    'q' => $query,
                    'type' => 'track',
                    'limit' => 10 // limit results to 10
                ]);

                $results = $response->json();

                // 3. extract raw data
                if (isset($results['tracks']['items'])) {
                    foreach ($results['tracks']['items'] as $track) {
                        $rawSongs[] = [
                            'title'       => $track['name'],
                            'creator'     => $track['artists'][0]['name'], // first artist
                            'releaseyear' => substr($track['album']['release_date'], 0, 4),
                            'coverimage'  => $track['album']['images'][0]['url'] ?? null,
                        ];
                    }
                }
            }
        }

        // format using FavoriteService for consistency
        $formattedSongs = $this->favoriteService->formatMusicData($rawSongs);

        // check if this is an AJAX request
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($formattedSongs);
        }

        return view('pages.music', ['songs' => $formattedSongs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'creator'     => 'required|string|max:255',
            'releaseyear' => 'required|integer',
            'coverimage'  => 'nullable|string|max:255',
        ]);

        $songId = null;
        $isNew = false;

        // check for duplicates
        $existingSong = DB::table('media')
            ->where('title', $validated['title'])
            ->where('creator', $validated['creator'])
            ->first();

        if ($existingSong) {
            // the song already exists, so we just use its ID.
            $songId = $existingSong->id;
        } else {
            // call the SQL function to create Media + Music atomically.
            $result = DB::select('SELECT fn_create_music(?, ?, ?, ?) as id', [
                $validated['title'],
                $validated['creator'],
                $validated['releaseyear'],
                $validated['coverimage']
            ]);

            $songId = $result[0]->id;
            $isNew = true;
        }

        $message = $isNew
            ? 'Song added to database! (ID: ' . $songId . ')'
            : 'Song found in library! (ID: ' . $songId . ')';

        return redirect('/music')->with('success', $message);
    }
}
