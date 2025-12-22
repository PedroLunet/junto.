<?php

namespace App\Http\Controllers\Media;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Media\MusicService;
use App\Services\FavoriteService;
use App\Models\Post\Post;
use App\Http\Controllers\Controller;

class MusicController extends Controller
{
    protected $musicService;
    protected $favoriteService;

    public function __construct(MusicService $musicService, FavoriteService $favoriteService)
    {
        $this->musicService = $musicService;
        $this->favoriteService = $favoriteService;
    }

    public function index()
    {
        $posts = Post::getMusicReviewPosts(auth()->id());
        return view('pages.home', [
            'posts' => $posts,
            'pageTitle' => 'Music Reviews'
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $query = $request->input('q');
        $formattedSongs = [];

        if ($query) {
            $results = $this->musicService->searchTracks($query, 10);

            if (isset($results['tracks']['items'])) {
                $formattedSongs = $this->musicService->formatMusicData($results['tracks']['items']);
            }
        }

        // check if this is an ajax request
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
            // Create Media + Music using Eloquent
            DB::transaction(function () use ($validated, &$songId) {
                $media = \App\Models\Media\Media::create([
                    'title' => $validated['title'],
                    'creator' => $validated['creator'],
                    'releaseyear' => $validated['releaseyear'],
                    'coverimage' => $validated['coverimage']
                ]);

                \App\Models\Media\Music::create([
                    'mediaid' => $media->id
                ]);

                $songId = $media->id;
            });
            $isNew = true;
        }

        $message = $isNew
            ? 'Song added to database! (ID: ' . $songId . ')'
            : 'Song found in library! (ID: ' . $songId . ')';

        return redirect('/music')->with('success', $message);
    }
}
