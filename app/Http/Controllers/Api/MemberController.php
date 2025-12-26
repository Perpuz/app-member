<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Get the authenticated User.
     */
    public function show()
    {
        return response()->json([
            'success' => true,
            'data' => auth('api')->user()
        ]);
    }
    
    /**
     * Update user profile.
     */
    public function update(Request $request)
    {
        $user = auth('api')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6|confirmed',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
}
