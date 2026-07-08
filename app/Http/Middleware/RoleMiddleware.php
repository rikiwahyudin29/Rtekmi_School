<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Get the active role from session, or default to the user's primary role
        $activeRole = session('role_active');
        
        if (!$activeRole && $request->user()) {
            $activeRole = $request->user()->role;
        }

        if (!$activeRole) {
            abort(403, 'Akses ditolak. Role tidak terdeteksi.');
        }

        // If 'admin' is required, allow 'superadmin' and 'admin'
        if (in_array('admin', $roles)) {
            $roles[] = 'superadmin';
            $roles[] = 'kepsek'; // Optional: allow headmaster
        }

        if (!in_array($activeRole, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin ke halaman ini.');
        }

        return $next($request);
    }
}
