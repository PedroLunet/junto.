<?php

namespace App\Http\Controllers;

use App\Services\MovieService;
use Illuminate\Http\Request;

class MovieController extends Controller {
    protected $movieService;

    public function __construct (MovieService $movieService){
        $this->movieService = $movieService;
    }

    public function index()
    {
        return view('pages.movies');
    }


    public function search(Request $request){
        $query = $request->get('q');

        if (empty($query)){
            return response() -> json([]);
        }

        $results = $this->movieService->searchMovies($query);

        return response()->json($results['results'] ?? []);

    }
}