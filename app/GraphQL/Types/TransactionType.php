<?php

namespace App\GraphQL\Types;

use App\Models\Transaction;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class TransactionType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Transaction',
        'description' => 'A borrowing transaction',
        'model' => Transaction::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id of the transaction',
            ],
            'status' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Status: borrowed, returned, overdue',
            ],
            'borrow_date' => [
                'type' => Type::string(),
            ],
            'due_date' => [
                'type' => Type::string(),
            ],
            'return_date' => [
                'type' => Type::string(),
            ],
            'fine_amount' => [
                'type' => Type::float(),
            ],
            // Relation
            'book' => [
                'type' => \Rebing\GraphQL\Support\Facades\GraphQL::type('Book'),
                'description' => 'The borrowed book',
            ],
        ];
    }
}
