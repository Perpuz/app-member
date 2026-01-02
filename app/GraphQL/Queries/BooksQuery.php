<?php

namespace App\GraphQL\Queries;

use App\Models\Book;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class BooksQuery extends Query
{
    protected $attributes = [
        'name' => 'books',
        'description' => 'A query to get list of books'
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Book'));
    }

    public function args(): array
    {
        return [
            'limit' => ['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 10],
            'page' => ['name' => 'page', 'type' => Type::int(), 'defaultValue' => 1],
        ];
    }

    public function resolve($root, $args)
    {
        return Book::with('category')
            ->limit($args['limit'])
            ->offset(($args['page'] - 1) * $args['limit'])
            ->get();
    }
}
