<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MusicService;

class MusicController extends Controller
{
    protected $musicService;

    public function __construct(MusicService $musicService)
    {
        $this->musicService = $musicService;
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $formattedSongs = [];

        if ($query) {
            $results = $this->musicService->searchTracks($query);

            if (isset($results['tracks']['items'])) {
                foreach ($results['tracks']['items'] as $track) {
                    $formattedSongs[] = [
                        'id'          => $track['id'],
                        'title'       => $track['name'],
                        'creator'     => $track['artists'][0]['name'], // first artist
                        'releaseyear' => substr($track['album']['release_date'], 0, 4),
                        'coverimage'  => $track['album']['images'][0]['url'] ?? null,
                    ];
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
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
