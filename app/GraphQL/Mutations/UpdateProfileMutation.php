<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Auth;

class UpdateProfileMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateProfile',
        'description' => 'Update user profile information'
    ];

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    public function args(): array
    {
        return [
            'name' => [
                'name' => 'name',
                'type' => Type::string(),
            ],
            'email' => [
                'name' => 'email',
                'type' => Type::string(),
            ],
            'phone' => [
                'name' => 'phone',
                'type' => Type::string(),
            ]
        ];
    }

    public function resolve($root, $args)
    {
        if (!auth('api')->check()) {
            throw new \Exception('Unauthorized');
        }

        $user = auth('api')->user();

        if (isset($args['name'])) $user->name = $args['name'];
        if (isset($args['email'])) $user->email = $args['email'];
        if (isset($args['phone'])) $user->phone = $args['phone'];

        $user->save();

        return $user;
    }
}
