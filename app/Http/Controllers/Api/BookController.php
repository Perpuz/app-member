<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookRecommendation;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     */
    public function index(Request $request)
    {
        // Sync from App-Librarian
        $externalUrl = env('EXTERNAL_API_URL'); 
        $integrationSecret = env('INTEGRATION_SECRET');

        if ($externalUrl && $integrationSecret) {
            try {
                $client = new \GuzzleHttp\Client();
                // Ensure URL ends with /api/integration/books
                $endpoint = rtrim($externalUrl, '/') . '/api/integration/books';
                
                $response = $client->request('GET', $endpoint, [
                    'headers' => [
                        'X-INTEGRATION-SECRET' => $integrationSecret,
                        'Accept' => 'application/json'
                    ],
                    'timeout' => 5, 
                    'verify' => false, 
                    'http_errors' => false
                ]);

                if ($response->getStatusCode() == 200) {
                    $books = json_decode($response->getBody(), true);
                    $books = isset($books['data']) ? $books['data'] : $books;

                    if (is_array($books)) {
                        // Ensure a default category exists
                        $defaultCategory = \App\Models\Category::firstOrCreate(
                            ['name' => 'General'],
                            ['description' => 'Imported Books']
                        );
                        
                        foreach ($books as $extBook) {
                            if (!isset($extBook['isbn']) || empty($extBook['isbn'])) {
                                $extBook['isbn'] = 'GEN-' . ($extBook['id'] ?? uniqid());
                            }
                            
                            Book::updateOrCreate(
                                ['isbn' => $extBook['isbn']], 
                                [
                                    'title' => $extBook['title'],
                                    'author' => $extBook['author'],
                                    'publisher' => $extBook['publisher'] ?? 'Unknown',
                                    'publication_year' => $extBook['publish_year'] ?? $extBook['published_year'] ?? $extBook['year'] ?? date('Y'),
                                    'category_id' => $defaultCategory->id,
                                    'description' => $extBook['description'] ?? '',
                                    'total_copies' => $extBook['stock'] ?? 10,
                                    'available_copies' => $extBook['stock'] ?? 10,
                                    'cover_image' => $extBook['cover_url'] ?? null
                                ]
                            );
                        }
                    }
                } else {
                     \Illuminate\Support\Facades\Log::error('Book Sync Failed. Status: ' . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Book Sync Exception: ' . $e->getMessage());
            }
        }

        $query = Book::with('category');

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }
        
        // Filter available books only if requested
        if ($request->boolean('available_only')) {
            $query->available();
        }

        $books = $query->latest()->paginate($request->input('limit', 12));

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Display the specified book.
     */
    public function show($id)
    {
        $book = Book::with(['category', 'transactions' => function($q) {
            $q->where('user_id', auth('api')->id())->latest();
        }])->findOrFail($id);
        
        // Check if current user has borrowed this book
        // Check if current user has borrowed this book
        $activeLoan = null;
        $userRating = null;
        
        if (auth('api')->check()) {
            $activeLoan = $book->transactions()
                ->where('user_id', auth('api')->id())
                ->active()
                ->first();
                
            $userRating = $book->bookRecommendations()
                ->where('user_id', auth('api')->id())
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $book,
            'user_status' => [
                'has_active_loan' => $activeLoan ? true : false,
                'loan_details' => $activeLoan,
                'rating' => $userRating ? $userRating->score : null
            ]
        ]);
    }
    
    /**
     * Search books (legacy/alias endpoint).
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Rate a book.
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|numeric|min:1|max:5'
        ]);

        $recommendation = BookRecommendation::updateOrCreate(
            [
                'user_id' => auth('api')->id(),
                'book_id' => $id
            ],
            [
                'score' => $request->score
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => $recommendation
        ]);
    }

    /**
     * Delete rating for a book.
     */
    public function unrate($id)
    {
        $deleted = BookRecommendation::where('user_id', auth('api')->id())
            ->where('book_id', $id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Rating removed successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Rating not found'
        ], 404);
    }
}
