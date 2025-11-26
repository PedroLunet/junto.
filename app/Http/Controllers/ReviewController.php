<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\MovieService;

class ReviewController extends Controller
{
    protected $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
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

            if ($request->input('type') === 'book') {
                $request->validate([
                    'google_book_id' => 'required|string',
                    'title' => 'required|string',
                    'creator' => 'required|string',
                ]);

                $title = $request->input('title');
                $creator = $request->input('creator');
                $releaseYear = $request->input('release_year');
                $coverImage = $request->input('cover_image');
                $mediaType = 'book';

            } elseif ($request->input('type') === 'movie') {
                $request->validate([
                    'tmdb_id' => 'required|integer',
                ]);

                // 1. get movie details from tmdb
                $tmdbMovie = $this->movieService->getMovie($request->tmdb_id);
                
                if (!$tmdbMovie || (isset($tmdbMovie['success']) && $tmdbMovie['success'] === false)) {
                    return response()->json(['success' => false, 'message' => 'Movie not found on TMDB'], 404);
                }

                $title = $tmdbMovie['title'];
                $releaseDate = $tmdbMovie['release_date'] ?? null;
                $releaseYear = $releaseDate ? (int)substr($releaseDate, 0, 4) : null;
                $posterPath = $tmdbMovie['poster_path'] ?? null;
                $coverImage = $posterPath ? "https://image.tmdb.org/t/p/w500{$posterPath}" : null;
                $mediaType = 'film';
                
                // Get Director
                $creator = "Unknown";
                if (isset($tmdbMovie['credits']['crew'])) {
                    foreach ($tmdbMovie['credits']['crew'] as $crew) {
                        if ($crew['job'] === 'Director') {
                            $creator = $crew['name'];
                            break;
                        }
                    }
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Media type not supported yet'], 400);
            }

            // 2. check if media exists in the db or create it
            $existingMedia = DB::selectOne("
                SELECT id FROM lbaw2544.media 
                WHERE title = ? AND releaseyear = ? AND creator = ?
            ", [$title, $releaseYear, $creator]);

            $mediaId = null;

            if ($existingMedia) {
                $mediaId = $existingMedia->id;
            } else {
                // create media
             
                $mediaId = DB::table('lbaw2544.media')->insertGetId([
                    'title' => $title,
                    'creator' => $creator,
                    'releaseyear' => $releaseYear,
                    'coverimage' => $coverImage
                ]);

                // create specific media entry
                if ($mediaType === 'book') {
                    DB::table('lbaw2544.book')->insert(['mediaid' => $mediaId]);
                } elseif ($mediaType === 'film') {
                    DB::table('lbaw2544.film')->insert(['mediaid' => $mediaId]);
                }
            }

            // 3. create post
            $postId = DB::table('lbaw2544.post')->insertGetId([
                'userid' => Auth::id(),
                'createdat' => now()
            ]);

            // 4. create review
            DB::table('lbaw2544.review')->insert([
                'postid' => $postId,
                'rating' => $request->rating,
                'mediaid' => $mediaId,
                'content' => $request->input('content')
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Review posted successfully',
                'post_id' => $postId
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
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

            // Check ownership
            $post = DB::selectOne("SELECT userId FROM lbaw2544.post WHERE id = ?", [$id]);
            
            if (!$post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            if ($post->userid !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Update Review
            DB::table('lbaw2544.review')
                ->where('postid', $id)
                ->update([
                    'rating' => $request->rating,
                    'content' => $request->input('content')
                ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Review updated successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
