<?php

namespace App\GraphQL\Types;

use App\Models\Book;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class BookType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Book',
        'description' => 'A book available in the library',
        'model' => Book::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id of the book',
            ],
            'title' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The title of the book',
            ],
            'author' => [
                'type' => Type::string(),
                'description' => 'The author of the book',
            ],
            'publisher' => [
                'type' => Type::string(),
            ],
            'publication_year' => [
                'type' => Type::int(),
            ],
            'isbn' => [
                'type' => Type::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'cover_image' => [
                'type' => Type::string(),
            ],
            'available_copies' => [
                'type' => Type::int(),
                'description' => 'Number of copies available for borrowing',
            ],
            'total_copies' => [
                'type' => Type::int(),
            ],
            // Relational Data
            'category' => [
                'type' => \Rebing\GraphQL\Support\Facades\GraphQL::type('Category'),
                'description' => 'The category of the book',
            ],
        ];
    }
}
