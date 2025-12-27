<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookSyncService
{
    protected string $apiUrl;
    protected int $timeout;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiUrl  = config('app.external_api_url', env('EXTERNAL_API_URL'));
        $this->baseUrl = preg_replace('#/api$#', '', $this->apiUrl);
        $this->timeout = (int) env('EXTERNAL_API_TIMEOUT', 30);
    }

    /**
     * Sinkronisasi data buku dari service librarian
     */
    public function syncBooks(): array
    {
        if (empty($this->apiUrl)) {
            Log::error('External API URL not configured');
            return [
                'success' => false,
                'message' => 'External API URL not configured'
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-INTEGRATION-SECRET' => env('INTEGRATION_SECRET'),
                    'Accept'              => 'application/json',
                ])
                ->get($this->apiUrl . '/books');

            if ($response->failed()) {
                Log::error('External API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'External API request failed',
                    'status'  => $response->status(),
                ];
            }

            $responseData = $response->json();

            // API bisa return: { data: [...] } atau langsung [...]
            $books = $responseData['data'] ?? $responseData;

            if (!is_array($books)) {
                Log::error('Invalid book data format', ['response' => $responseData]);
                return [
                    'success' => false,
                    'message' => 'Invalid book data format'
                ];
            }

            $count = 0;

            foreach ($books as $bookData) {
                $this->processBook($bookData);
                $count++;
            }

            return [
                'success' => true,
                'message' => "Successfully synced {$count} books",
                'count'   => $count,
            ];

        } catch (\Throwable $e) {
            Log::error('Exception during book sync', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Proses satu data buku dari API
     */
    protected function processBook(array $data): void
    {
        if (empty($data['isbn'])) {
            Log::warning('Book skipped: ISBN is missing', [
                'external_id' => $data['id'] ?? null,
                'title' => $data['title'] ?? null,
            ]);
            return;
        }

        $categoryName = $data['category']
            ?? $data['category_name']
            ?? 'General';

        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['description' => 'Imported from librarian service']
        );

        Book::updateOrCreate(
            ['isbn' => $data['isbn']],
            [
                'external_id'      => (string) $data['id'],
                'title'            => $data['title'],
                'author'           => $data['author'] ?? null,
                'publisher'        => $data['publisher'] ?? null,
                'publication_year' => $data['publication_year'] ?? null,
                'description'      => $data['description'] ?? null,
                'cover_image' => $data['cover_url'] ?? null,
                'category_id'      => $category->id,
                'total_copies'     => $data['stock'] ?? 0,
                'available_copies' => Book::where('isbn', $data['isbn'])->value('available_copies')
                    ?? ($data['stock'] ?? 0),
            ]
        );
    }

}
