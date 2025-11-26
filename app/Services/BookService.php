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

    public function searchBooks($query)
    {
        $queryParams = [
            'q' => $query,
            'maxResults' => 10,
        ];

        if ($this->apiKey) {
            $queryParams['key'] = $this->apiKey;
        }

        $response = Http::get("{$this->baseUrl}/volumes", $queryParams);

        return $response->json();
    }

    public function getBook($id)
    {
        $queryParams = [];
        if ($this->apiKey) {
            $queryParams['key'] = $this->apiKey;
        }

        $response = Http::get("{$this->baseUrl}/volumes/{$id}", $queryParams);

        return $response->json();
    }
}
