<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Vite;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(32);
        
        // Let Vite know about the nonce so it can inject it into scripts/styles it loads
        Vite::useCspNonce($nonce);
        
        // Share nonce to all views so we can use it manually
        view()->share('cspNonce', $nonce);

        $response = $next($request);

        // Apply headers only to typical responses, ignore binary downloads etc. if any
        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            
            // Define strict CSP
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'strict-dynamic' 'unsafe-inline'; " .
                   // Included 'unsafe-inline' for styles because Vue/Inertia often injects dynamic styles which might break without it
                   "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://fonts.bunny.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
                   "font-src 'self' https://fonts.bunny.net https://cdnjs.cloudflare.com https://fonts.gstatic.com data:; " .
                   "img-src 'self' data: https: blob:; " .
                   "connect-src 'self' https://api.fonnte.com https://*.tripay.co.id wss: ws:; " .
                   "object-src 'none'; " .
                   "frame-ancestors 'self'; " .
                   "form-action 'self'; " .
                   "base-uri 'self'; " .
                   "upgrade-insecure-requests;";

            $response->header('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
