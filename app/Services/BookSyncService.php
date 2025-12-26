<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookSyncService
{
    protected $apiUrl;
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('app.external_api_url', env('EXTERNAL_API_URL'));
        $this->timeout = config('app.external_api_timeout', env('EXTERNAL_API_TIMEOUT', 30));
    }

    public function syncBooks()
    {
        if (empty($this->apiUrl)) {
            Log::error('External API URL not configured.');
            return ['success' => false, 'message' => 'External API URL not configured.'];
        }

        try {
            $response = Http::timeout($this->timeout)->get($this->apiUrl . '/books');

            if ($response->failed()) {
                Log::error('Failed to fetch books from external API: ' . $response->body());
                return ['success' => false, 'message' => 'Failed to connect to external API.'];
            }

            $booksData = $response->json();
            // Handle if data is wrapped in 'data' key or root array
            $books = $booksData['data'] ?? $booksData;

            if (!is_array($books)) {
                return ['success' => false, 'message' => 'Invalid data format received.'];
            }

            $count = 0;
            foreach ($books as $bookData) {
                $this->processBook($bookData);
                $count++;
            }

            return ['success' => true, 'message' => "Successfully synced $count books.", 'count' => $count];

        } catch (\Exception $e) {
            Log::error('Exception during book sync: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    protected function processBook($data)
    {
        // Handle Category
        // Assuming API sends 'category_name' or similar, strict mapping might be needed
        // For now, we try to match by name or default to 'Uncategorized'
        $categoryName = $data['category'] ?? $data['category_name'] ?? 'General';
        
        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['description' => 'Imported category']
        );

        // Update or Create Book
        // Using external_id as the unique key for sync
        Book::updateOrCreate(
            ['external_id' => $data['id']], // Assuming 'id' from external API is the unique key
            [
                'title' => $data['title'],
                'author' => $data['author'],
                'publisher' => $data['publisher'] ?? null,
                'publication_year' => $data['publication_year'] ?? $data['year'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'description' => $data['description'] ?? null,
                'total_copies' => $data['stock'] ?? $data['total_copies'] ?? 0,
                'available_copies' => $data['available'] ?? $data['available_copies'] ?? 0,
                'cover_image' => $data['cover_image'] ?? null,
                'category_id' => $category->id
            ]
        );
    }
}
