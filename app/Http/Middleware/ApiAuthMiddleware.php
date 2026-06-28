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
        
        // Kunci API WAJIB ada di .env. Jika tidak ada, tolak semua request API demi keamanan!
        if (empty($expectedKey)) {
            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error. API_SECRET_KEY belum dikonfigurasi di server.'
            ], 500);
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
