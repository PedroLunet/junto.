<?php

namespace App\Http\Controllers;

use App\Services\MovieService;
use App\Services\FavoriteService;
use App\Models\Post;
use Illuminate\Http\Request;

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
            'pageTitle' => 'Movies'
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

        // enable director fetching
        $formattedMovies = $this->movieService->formatMovieData($movies, true);

        return response()->json($formattedMovies);
    }
}
