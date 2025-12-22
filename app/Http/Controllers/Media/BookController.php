<?php

namespace App\Http\Controllers\Media;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Media\BookService;
use App\Services\FavoriteService;
use App\Models\Post\Post;
use App\Http\Controllers\Controller;

class BookController extends Controller
{
    protected $bookService;
    protected $favoriteService;

    public function __construct(BookService $bookService, FavoriteService $favoriteService)
    {
        $this->bookService = $bookService;
        $this->favoriteService = $favoriteService;
    }

    public function index()
    {
        $posts = Post::getBookReviewPosts(auth()->id());
        return view('pages.home', [
            'posts' => $posts,
            'pageTitle' => 'Book Reviews'
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $query = $request->input('q');
        $formattedBooks = [];

        if ($query) {
            $results = $this->bookService->searchBooks($query, 10);

            if (isset($results['items'])) {
                $formattedBooks = $this->bookService->formatBookData($results['items']);
            }
        }

        // check if it's an AJAX request
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($formattedBooks);
        }

        return view('pages.books', ['books' => $formattedBooks]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'creator' => 'required|string|max:255',
            'releaseyear' => 'nullable|integer',
            'coverimage' => 'nullable|string|max:255',
        ]);

        $bookId = null;
        $isNew = false;

        // check for duplicates
        $existingBook = DB::table('media')
            ->where('title', $validated['title'])
            ->where('creator', $validated['creator'])
            ->first();

        if ($existingBook) {
            // book already exists -> we just use its ID.
            $bookId = $existingBook->id;
        } else {
            // Create Media + Book using Eloquent
            DB::transaction(function () use ($validated, &$bookId) {
                $media = \App\Models\Media\Media::create([
                    'title' => $validated['title'],
                    'creator' => $validated['creator'],
                    'releaseyear' => $validated['releaseyear'],
                    'coverimage' => $validated['coverimage']
                ]);

                \App\Models\Media\Book::create([
                    'mediaid' => $media->id
                ]);

                $bookId = $media->id;
            });
            $isNew = true;
        }

        $message = $isNew
            ? 'Book added to database! (ID: ' . $bookId . ')'
            : 'Book found in library! (ID: ' . $bookId . ')';

        return redirect('/books')->with('success', $message);
    }
}
