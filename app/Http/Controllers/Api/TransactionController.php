<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the user's transactions.
     */
    public function index(Request $request)
    {
        try {
            $query = Transaction::with('book')
                ->where('user_id', auth('api')->id());
                
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $transactions = $query->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Transaction Index Error: " . $e->getMessage() . "\n", FILE_APPEND);
            return response()->json(['message' => 'Failed to load transactions: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Display the specified transaction.
     */
    public function show($id)
    {
        $transaction = Transaction::with(['book', 'user'])
            ->where('user_id', auth('api')->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Borrow a book.
     */
    public function borrow(Request $request)
    {
        @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Borrow Endpoint Hit. Data: " . json_encode($request->all()) . "\n", FILE_APPEND);
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'book_id' => 'required|exists:books,id',
                'duration' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Validation Failed: " . json_encode($validator->errors()) . "\n", FILE_APPEND);
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }
            
             @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Validation Passed\n", FILE_APPEND);

            $book = Book::find($request->book_id);

            if (!$book->isAvailable()) {
                return response()->json(['message' => 'Book is not available'], 400);
            }

            // Check if user has active transaction for this book
            $existingLoan = Transaction::where('user_id', auth('api')->id())
                ->where('book_id', $book->id)
                ->active()
                ->first();
                
            if ($existingLoan) {
                 return response()->json(['message' => 'You already have an active loan for this book'], 400);
            }

            $duration = (int) $request->input('duration', 7);
            $transaction = Transaction::create([
                'user_id' => auth('api')->id(),
                'book_id' => $book->id,
                'borrow_date' => now(),
                'due_date' => now()->addDays($duration),
                'status' => 'borrowed'
            ]);

            $book->decreaseAvailability();
            
            // Sync Borrow
            $this->syncToLibrarian($transaction, $book);
            
            @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Borrow Success\n", FILE_APPEND);

            return response()->json([
                'success' => true,
                'message' => 'Book borrowed successfully',
                'data' => $transaction
            ], 201);
        } catch (\Throwable $e) {
             @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Borrow Error (Throwable): " . $e->getMessage() . "\n", FILE_APPEND);
             return response()->json(['message' => 'Failed to borrow book: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Return a book.
     */
    public function returnBook($id)
    {
        $transaction = Transaction::where('user_id', auth('api')->id())
            ->active()
            ->findOrFail($id);

        $transaction->processReturn();
        
        // Sync Return
        $this->syncToLibrarian($transaction, $transaction->book, 'returned');

        return response()->json([
            'success' => true,
            'message' => 'Book returned successfully',
            'data' => $transaction
        ]);
    }

    private function syncToLibrarian($transaction, $book = null, $status = 'borrowed')
    {
        try {
            $url = env('EXTERNAL_API_URL');
            $secret = env('INTEGRATION_SECRET');
            $user = auth('api')->user();

            if ($url && $secret && $user) {
                if(!$book) $book = $transaction->book; // lazy load if needed

                $client = new \GuzzleHttp\Client();
                
                // Robust URL Construction (Localhost Fix)
                $baseUrl = rtrim($url, '/');
                $baseUrl = str_replace('127.0.0.1', 'localhost', $baseUrl);

                if (substr($baseUrl, -4) === '/api') {
                    $endpoint = $baseUrl . '/integration/sync/transaction';
                } else {
                    $endpoint = $baseUrl . '/api/integration/sync/transaction';
                }
                
                $payload = [
                    'nim' => $user->nim,
                    'book_isbn' => $book->isbn,
                    'book_title' => $book->title,
                    'borrow_date' => $transaction->borrow_date->format('Y-m-d'),
                    'due_date' => $transaction->due_date->format('Y-m-d'),
                    'status' => $status
                ];

                if ($status === 'returned') {
                    $payload['return_date'] = date('Y-m-d');
                    // Map fine_amount to 'fine' for Librarian App
                    $payload['fine'] = $transaction->fine_amount ?? 0;
                }

                $client->post($endpoint, [
                    'headers' => [
                        'X-INTEGRATION-SECRET' => $secret,
                        'Accept' => 'application/json'
                    ],
                    'json' => $payload,
                    'http_errors' => false,
                    'timeout' => 2
                ]);
            }
        } catch (\Exception $e) {
            @file_put_contents(public_path('transaction_debug.txt'), date('Y-m-d H:i:s') . " - Sync Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
