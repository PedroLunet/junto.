<?php

namespace App\Http\Controllers\Media;

use App\Services\Media\MovieService;
use App\Services\FavoriteService;
use App\Models\Post\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MovieController extends Controller
{
    protected $movieService;
    protected $favoriteService;

    public function __construct(MovieService $movieService, FavoriteService $favoriteService)
    {
        $this->movieService = $movieService;
        $this->favoriteService = $favoriteService;
    }

    public function index()
    {
        $posts = Post::getMovieReviewPosts(auth()->id());
        return view('pages.home', [
            'posts' => $posts,
            'pageTitle' => 'Movie Reviews'
        ]);
    }


    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $query = $request->get('q');

        $results = $this->movieService->searchMovies($query);
        $movies = $results['results'] ?? [];

        // disable director fetching for search results to improve performance
        $formattedMovies = $this->movieService->formatMovieData($movies, false);

        return response()->json($formattedMovies);
    }
}
