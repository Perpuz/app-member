<?php

namespace App\GraphQL\Queries;

use App\Models\Book;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class BookQuery extends Query
{
    protected $attributes = [
        'name' => 'book',
        'description' => 'A query to get a single book'
    ];

    public function type(): Type
    {
        return GraphQL::type('Book');
    }

    public function args(): array
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::nonNull(Type::id())],
        ];
    }

    public function resolve($root, $args)
    {
        return Book::with('category')->findOrFail($args['id']);
    }
}
