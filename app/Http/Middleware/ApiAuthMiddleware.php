<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $expectedKey = env('API_SECRET_KEY');
        
        // Jika API_SECRET_KEY belum diset di .env, loloskan sementara untuk backward compatibility
        // Namun sebaiknya di production harus di set.
        if (empty($expectedKey)) {
            return $next($request);
        }

        $providedKey = $request->header('X-API-KEY') ?? $request->input('api_key');

        if ($providedKey !== $expectedKey) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. API Key tidak valid atau tidak ditemukan.'
            ], 401);
        }

        return $next($request);
    }
}
