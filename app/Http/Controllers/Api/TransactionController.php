<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowBookRequest;
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
    public function borrow(BorrowBookRequest $request)
    {
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

        $duration = $request->input('duration', 7);
        $transaction = Transaction::create([
            'user_id' => auth('api')->id(),
            'book_id' => $book->id,
            'borrow_date' => now(),
            'due_date' => now()->addDays($duration),
            'status' => 'borrowed'
        ]);

        $book->decreaseAvailability();

        return response()->json([
            'success' => true,
            'message' => 'Book borrowed successfully',
            'data' => $transaction
        ], 201);
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

        return response()->json([
            'success' => true,
            'message' => 'Book returned successfully',
            'data' => $transaction
        ]);
    }
}
