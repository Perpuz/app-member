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
    /**
     * Update user details from Admin Portal.
     */
    public function updateUser($nim, Request $request)
    {
        $user = User::where('nim', $nim)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Validate basic fields
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6'
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['email'])) $updateData['email'] = $validated['email'];
        if (isset($validated['password'])) $updateData['password'] = bcrypt($validated['password']);

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }
}
