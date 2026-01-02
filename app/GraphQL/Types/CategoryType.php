<?php

namespace App\GraphQL\Types;

use App\Models\Category;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class CategoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Category',
        'description' => 'A category of books',
        'model' => Category::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id of the category',
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The name of the category',
            ],
        ];
    }
}
