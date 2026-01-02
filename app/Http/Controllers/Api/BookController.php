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
        $externalUrl = env('EXTERNAL_API_URL'); // e.g., http://localhost:8081
        $integrationSecret = env('INTEGRATION_SECRET');

        if ($externalUrl && $integrationSecret) {
            try {
                $client = new \GuzzleHttp\Client();
                // If EXTERNAL_API_URL ends in /api, don't append /api again
                $baseUrl = rtrim($externalUrl, '/');
                
                // Force localhost if 127.0.0.1 is used (Fix connection refused on some Windows setups)
                $baseUrl = str_replace('127.0.0.1', 'localhost', $baseUrl);

                if (substr($baseUrl, -4) === '/api') {
                    $endpoint = $baseUrl . '/integration/books';
                } else {
                    $endpoint = $baseUrl . '/api/integration/books';
                }
                
                // Debug Log to Public File
                @file_put_contents(public_path('book_sync_debug.txt'), date('Y-m-d H:i:s') . " - Attempting Sync from: $endpoint\n", FILE_APPEND);

                $response = $client->request('GET', $endpoint, [
                    'headers' => [
                        'X-INTEGRATION-SECRET' => $integrationSecret,
                        'Accept' => 'application/json'
                    ],
                    'timeout' => 5, // Increased timeout
                    'verify' => false, // Disable SSL verify for localhost
                    'http_errors' => false // Catch 404/500 manually
                ]);

                if ($response->getStatusCode() == 200) {
                    $books = json_decode($response->getBody(), true);
                    
                    // Handle Wrapped vs Direct
                    $books = isset($books['data']) ? $books['data'] : $books;

                    if (is_array($books)) {
                        $countObj = 0;
                        foreach ($books as $extBook) {
                            if (!isset($extBook['isbn']) || empty($extBook['isbn'])) {
                                // Fallback ISBN if missing or empty
                                $extBook['isbn'] = 'GEN-' . ($extBook['id'] ?? uniqid());
                            }
                            
                            // Fix: Interpret stock from App-Librarian as AVAILABLE copies, not Total.
                            // We calculate Total by adding our locally active loans to the remote available stock.
                            
                            $remoteAvailable = $extBook['stock'] ?? 0;
                            
                            // Check for existing book to count active loans
                            $localBook = Book::where('isbn', $extBook['isbn'])->first();
                            $activeLoans = $localBook ? $localBook->transactions()->where('status', 'borrowed')->count() : 0;
                            
                            $impliedTotal = $remoteAvailable + $activeLoans;
                            
                            $book = Book::updateOrCreate(
                                ['isbn' => $extBook['isbn']], 
                                [
                                    'title' => $extBook['title'],
                                    'author' => $extBook['author'],
                                    'total_copies' => $impliedTotal, // Use calculated total
                                    'available_copies' => $remoteAvailable, // Use remote stock as available
                                    'category_id' => 1,
                                    'publisher' => $extBook['publisher'] ?? 'Unknown',
                                    'publication_year' => $extBook['publish_year'] ?? $extBook['published_year'] ?? date('Y'),
                                    'cover_image' => $extBook['cover_url'] ?? null
                                ]
                            );
                            $countObj++;
                        }
                        // Log success
                         @file_put_contents(public_path('book_sync_debug.txt'), " - Success: Synced $countObj books\n", FILE_APPEND);
                    }
                } else {
                     @file_put_contents(public_path('book_sync_debug.txt'), " - Failed: HTTP " . $response->getStatusCode() . " Body: " . substr($response->getBody(), 0, 100) . "\n", FILE_APPEND);
                     \Illuminate\Support\Facades\Log::error('Book Sync Failed. Status: ' . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                // Log full error
                @file_put_contents(public_path('book_sync_debug.txt'), " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
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
    /**
     * Store book from Integration (Push Sync).
     */
    public function storeFromIntegration(Request $request)
    {
        $data = $request->validate([
            'isbn' => 'nullable|string',
            'title' => 'required|string',
            'author' => 'nullable|string',
            'stock' => 'required|numeric',
            'publisher' => 'nullable|string',
            'publication_year' => 'nullable|digits:4',
            'cover_url' => 'nullable|string'
        ]);

        if (empty($data['isbn'])) {
            $data['isbn'] = 'GEN-' . uniqid();
        }

        // Fix: Stock from integration is AVAILABLE stock
        $remoteAvailable = $data['stock'];
        
        $localBook = Book::where('isbn', $data['isbn'])->first();
        $activeLoans = $localBook ? $localBook->transactions()->where('status', 'borrowed')->count() : 0;
        
        $impliedTotal = $remoteAvailable + $activeLoans;

        $book = Book::updateOrCreate(
            ['isbn' => $data['isbn']], 
            [
                'title' => $data['title'],
                'author' => $data['author'],
                'total_copies' => $impliedTotal,
                'available_copies' => $remoteAvailable,
                'category_id' => 1,
                'publisher' => $data['publisher'] ?? 'Unknown',
                'publication_year' => $data['publication_year'] ?? date('Y'),
                'cover_image' => $data['cover_url'] ?? null
            ]
        );

        return response()->json(['success' => true, 'data' => $book]);
    }
}
