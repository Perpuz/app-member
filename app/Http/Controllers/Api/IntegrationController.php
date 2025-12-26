<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Get list of all registered users.
     * This endpoint is intended for the Admin Portal integration.
     */
    public function getUsers()
    {
        // Fetch users with relevant fields for the admin
        $users = User::select('id', 'nim', 'name', 'email', 'phone', 'address', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $users->count(),
            'data' => $users
        ]);
    }
}
