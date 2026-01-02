<?php

namespace App\GraphQL\Mutations;

use App\Models\Book;
use App\Models\Transaction;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BorrowBookMutation extends Mutation
{
    protected $attributes = [
        'name' => 'borrowBook',
        'description' => 'Borrow a book for a specific duration'
    ];

    public function type(): Type
    {
        return GraphQL::type('Transaction');
    }

    public function args(): array
    {
        return [
            'book_id' => [
                'name' => 'book_id',
                'type' => Type::nonNull(Type::id()),
            ],
            'duration' => [
                'name' => 'duration',
                'type' => Type::nonNull(Type::int()),
                'description' => 'Duration in days (e.g. 7, 14)'
            ]
        ];
    }

    public function resolve($root, $args)
    {
        if (!auth('api')->check()) {
            throw new \Exception('Unauthorized');
        }

        $book = Book::findOrFail($args['book_id']);
        $user = auth('api')->user();

        // Check if user already has an active loan for this book
        $existingLoan = Transaction::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingLoan) {
            throw new \Exception('You already have an active loan for this book.');
        }

        if (!$book->isAvailable()) {
            throw new \Exception('Book is currently out of stock.');
        }

        // Create Transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays($args['duration']),
            'status' => 'borrowed'
        ]);

        // Decrease availability
        $book->decreaseAvailability();

        return $transaction;
    }
}
