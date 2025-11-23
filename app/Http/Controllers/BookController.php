<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
  public function search(Request $request)
  {
    $query = $request->input('q');
    $formattedBooks = [];

    if ($query) {
      // Google Books API doesn't require authentication for basic searches
      $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
        'q' => $query,
        'maxResults' => 10, // limit results to 10
        'key' => config('services.google_books.api_key'), // optional, for higher quotas
      ]);

      $results = $response->json();

      // Format the data
      if (isset($results['items'])) {
        foreach ($results['items'] as $book) {
          $volumeInfo = $book['volumeInfo'];

          $formattedBooks[] = [
            'title' => $volumeInfo['title'] ?? 'Unknown Title',
            'creator' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : 'Unknown Author',
            'releaseyear' => isset($volumeInfo['publishedDate']) ? substr($volumeInfo['publishedDate'], 0, 4) : null,
            'coverimage' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
          ];
        }
      }
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

    // Check for duplicates
    $existingBook = DB::table('media')
      ->where('title', $validated['title'])
      ->where('creator', $validated['creator'])
      ->first();

    if ($existingBook) {
      // The book already exists, so we just use its ID.
      $bookId = $existingBook->id;
    } else {
      // Call the SQL function to create Media + Book atomically.
      $result = DB::select('SELECT fn_create_book(?, ?, ?, ?) as id', [
        $validated['title'],
        $validated['creator'],
        $validated['releaseyear'],
        $validated['coverimage']
      ]);

      $bookId = $result[0]->id;
      $isNew = true;
    }

    $message = $isNew
      ? 'Book added to database! (ID: ' . $bookId . ')'
      : 'Book found in library! (ID: ' . $bookId . ')';

    return redirect('/books')->with('success', $message);
  }
}
