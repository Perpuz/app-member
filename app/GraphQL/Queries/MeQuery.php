<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Auth;

class MeQuery extends Query
{
    protected $attributes = [
        'name' => 'me',
        'description' => 'A query to get authenticated user information'
    ];

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    public function resolve($root, $args)
    {
        if (!auth('api')->check()) {
            return null;
        }
        return auth('api')->user();
    }
}
