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
        
        // Sync to Librarian App
        $this->syncToLibrarian($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    private function syncToLibrarian($user)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $url = env('EXTERNAL_API_URL') . '/api/integration/sync/member';
            // Force localhost if needed
            $url = str_replace('127.0.0.1', 'localhost', $url);
            
            $payload = [
                'nim' => $user->nim ?? $user->member_code ?? ('M' . $user->id),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone
            ];

            $client->post($url, [
                'headers' => [
                    'X-INTEGRATION-SECRET' => env('INTEGRATION_SECRET'),
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload,
                'verify' => false,
                'http_errors' => false,
                'timeout' => 2 
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Member Sync Fail: ' . $e->getMessage());
        }
    }
}
