<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BookService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.google_books.api_key');
        $this->baseUrl = 'https://www.googleapis.com/books/v1';
    }

    public function searchBooks($query, $maxResults = 10)
    {
        $params = [
            'q' => $query,
            'maxResults' => $maxResults,
        ];

        // add API key if configured
        if ($this->apiKey) {
            $params['key'] = $this->apiKey;
        }

        $response = Http::get("{$this->baseUrl}/volumes", $params);

        return $response->json();
    }

    public function getBook($id)
    {
        $params = [];

        // add API key if configured
        if ($this->apiKey) {
            $params['key'] = $this->apiKey;
        }

        $response = Http::get("{$this->baseUrl}/volumes/{$id}", $params);

        return $response->json();
    }

    // format raw Google Books API data into standardized format
    public function formatBookData(array $googleBooksItems): array
    {
        $formattedBooks = [];

        foreach ($googleBooksItems as $book) {
            $volumeInfo = $book['volumeInfo'] ?? [];

            $formattedBooks[] = [
                'id' => $book['id'] ?? null,
                'title' => $volumeInfo['title'] ?? 'Unknown Title',
                'creator' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : 'Unknown Author',
                'releaseYear' => isset($volumeInfo['publishedDate']) ? substr($volumeInfo['publishedDate'], 0, 4) : null,
                'coverImage' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
                'releaseyear' => isset($volumeInfo['publishedDate']) ? substr($volumeInfo['publishedDate'], 0, 4) : null,
                'coverimage' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
                'type' => 'book'
            ];
        }

        return $formattedBooks;
    }
}
