<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockDangerousFiles
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
        // Daftar ekstensi file yang diblokir untuk keamanan (RCE prevention)
        $dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'cmd', 'cgi', 'pl', 'jsp', 'asp', 'aspx', 'py', 'rb'];

        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    if ($this->isDangerous($f, $dangerousExtensions)) {
                        return response()->json(['status' => false, 'message' => 'Upload file dengan tipe ini diblokir demi keamanan.'], 403);
                    }
                }
            } else {
                if ($this->isDangerous($file, $dangerousExtensions)) {
                    // Kalau web request biasa, kembalikan abort. Kalau API kembalikan JSON
                    if ($request->is('api/*') || $request->wantsJson()) {
                        return response()->json(['status' => false, 'message' => 'Upload file dengan tipe ini diblokir demi keamanan.'], 403);
                    }
                    abort(403, 'Upload file dengan tipe ini diblokir demi keamanan sistem.');
                }
            }
        }

        return $next($request);
    }

    private function isDangerous($file, $dangerousExtensions)
    {
        if (!$file) return false;
        
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Cek juga nama aslinya untuk menghindari null-byte atau double extension (shell.php.jpg)
        $originalName = strtolower($file->getClientOriginalName());
        $hasPhp = (strpos($originalName, '.php') !== false || strpos($originalName, '.phtml') !== false);
        
        return in_array($extension, $dangerousExtensions) || $hasPhp;
    }
}
