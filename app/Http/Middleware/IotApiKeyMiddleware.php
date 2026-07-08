<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IotApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Validasi API Key
        // Idealnya diambil dari .env, kita hardcode sementara atau ambil dari config
        $validApiKey = env('IOT_API_KEY', 'siakad-iot-secret-key-2024');
        
        $apiKey = $request->header('X-API-KEY') ?? $request->input('api_key');

        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid API Key'
            ], 401);
        }

        return $next($request);
    }
}
