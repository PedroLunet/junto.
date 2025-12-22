<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use App\Services\Media\BookService;
use App\Services\Media\MovieService;
use App\Services\Media\MusicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    protected $movieService;

    protected $bookService;

    protected $musicService;

    public function __construct(MovieService $movieService, BookService $bookService, MusicService $musicService)
    {
        $this->movieService = $movieService;
        $this->bookService = $bookService;
        $this->musicService = $musicService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
            'type' => 'required|string|in:movie,book,music',
        ]);

        try {
            DB::beginTransaction();

            $title = null;
            $creator = null;
            $releaseYear = null;
            $coverImage = null;
            $mediaType = null;
            $groupId = $request->input('group_id');

            if ($request->input('type') === 'book') {
                $request->validate([
                    'google_book_id' => 'required|string',
                ]);

                $googleBook = $this->bookService->getBook($request->google_book_id);

                if (! $googleBook || isset($googleBook['error'])) {
                    return response()->json(['success' => false, 'message' => 'Book not found on Google Books'], 404);
                }

                $volumeInfo = $googleBook['volumeInfo'];
                $title = $volumeInfo['title'] ?? 'Unknown Title';
                $creator = isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : 'Unknown Author';
                $releaseDate = $volumeInfo['publishedDate'] ?? null;
                $releaseYear = $releaseDate ? (int) substr($releaseDate, 0, 4) : null;
                $coverImage = $volumeInfo['imageLinks']['thumbnail'] ?? null;

                if ($coverImage) {
                    $coverImage = str_replace('http://', 'https://', $coverImage);
                }

                $mediaType = 'book';
            } elseif ($request->input('type') === 'movie') {
                $request->validate([
                    'tmdb_id' => 'required|integer',
                ]);

                // 1. get movie details from tmdb
                $tmdbMovie = $this->movieService->getMovie($request->tmdb_id);

                if (! $tmdbMovie || (isset($tmdbMovie['success']) && $tmdbMovie['success'] === false)) {
                    return response()->json(['success' => false, 'message' => 'Movie not found on TMDB'], 404);
                }

                $title = $tmdbMovie['title'];
                $releaseDate = $tmdbMovie['release_date'] ?? null;
                $releaseYear = $releaseDate ? (int) substr($releaseDate, 0, 4) : null;
                $posterPath = $tmdbMovie['poster_path'] ?? null;
                $coverImage = $posterPath ? "https://image.tmdb.org/t/p/w500{$posterPath}" : null;
                $mediaType = 'film';

                // Get Director
                $creator = 'Unknown';
                if (isset($tmdbMovie['credits']['crew'])) {
                    foreach ($tmdbMovie['credits']['crew'] as $crew) {
                        if ($crew['job'] === 'Director') {
                            $creator = $crew['name'];
                            break;
                        }
                    }
                }
            } elseif ($request->input('type') === 'music') {
                $request->validate([
                    'spotify_id' => 'required|string',
                ]);

                $track = $this->musicService->getTrack($request->spotify_id);

                if (! $track || isset($track['error'])) {
                    return response()->json(['success' => false, 'message' => 'Track not found on Spotify'], 404);
                }

                $title = $track['name'];
                $creator = $track['artists'][0]['name'] ?? 'Unknown Artist';
                $releaseDate = $track['album']['release_date'] ?? null;
                $releaseYear = $releaseDate ? (int) substr($releaseDate, 0, 4) : null;
                $coverImage = $track['album']['images'][0]['url'] ?? null;
                $mediaType = 'music';
            } else {
                return response()->json(['success' => false, 'message' => 'Media type not supported yet'], 400);
            }

            // 2. check if media exists in the db or create it
            $existingMedia = DB::selectOne('
                SELECT id FROM media 
                WHERE title = ? AND releaseyear = ? AND creator = ?
            ', [$title, $releaseYear, $creator]);

            $mediaId = null;

            if ($existingMedia) {
                $mediaId = $existingMedia->id;
            } else {
                // create media

                $mediaId = DB::table('media')->insertGetId([
                    'title' => $title,
                    'creator' => $creator,
                    'releaseyear' => $releaseYear,
                    'coverimage' => $coverImage,
                ]);

                // create specific media entry
                if ($mediaType === 'book') {
                    DB::table('book')->insert(['mediaid' => $mediaId]);
                } elseif ($mediaType === 'film') {
                    DB::table('film')->insert(['mediaid' => $mediaId]);
                } elseif ($mediaType === 'music') {
                    DB::table('music')->insert(['mediaid' => $mediaId]);
                }
            }

            // 3. create post
            $postData = [
                'userid' => Auth::id(),
                'createdat' => now(),
            ];
            if ($groupId) {
                $postData['groupid'] = $groupId;
            }
            $postId = DB::table('post')->insertGetId($postData);

            // 4. create review
            DB::table('review')->insert([
                'postid' => $postId,
                'rating' => $request->rating,
                'mediaid' => $mediaId,
                'content' => $request->input('content'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review posted successfully',
                'post_id' => $postId,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'Server Error: '.$e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $post = Post::findOrFail($id);
            $this->authorize('update', $post);

            // Update Review
            DB::table('review')
                ->where('postid', $id)
                ->update([
                    'rating' => $request->rating,
                    'content' => $request->input('content'),
                ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Review updated successfully']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'Server Error: '.$e->getMessage()], 500);
        }
    }
}
