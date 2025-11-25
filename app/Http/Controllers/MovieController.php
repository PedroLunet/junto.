<?php

namespace App\Http\Controllers;

use App\Services\MovieService;
use App\Services\FavoriteService;
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
        return view('pages.movies');
    }


    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $query = $request->get('q');

        $results = $this->movieService->searchMovies($query);
        $movies = $results['results'] ?? [];

        $formattedMovies = $this->movieService->formatMovieData($movies);

        return response()->json($formattedMovies);
    }
}
