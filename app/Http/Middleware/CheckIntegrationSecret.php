<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIntegrationSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('app.integration_secret', env('INTEGRATION_SECRET'));
        
        // If no secret is configured, deny all requests for security
        if (empty($secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Integration secret not configured on server'
            ], 500);
        }

        $providedSecret = $request->header('X-INTEGRATION-SECRET');

        if ($providedSecret !== $secret) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid integration secret'
            ], 401);
        }

        return $next($request);
    }
}
