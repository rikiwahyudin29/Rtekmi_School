<?php

namespace App\Services;

use App\Models\LogKeuangan;
use Illuminate\Support\Facades\Http;

class LogKeuanganService
{
    public static function catat($aksi)
    {
        $ip = request()->ip();
        
        $lokasi = 'Localhost / Private Network';
        
        // Cek apakah IP public (bukan 127.0.0.1 atau subnet privat)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            try {
                // Gunakan ip-api untuk mendeteksi lokasi (timeout 2 detik agar tidak lemot)
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful() && $response->json('status') === 'success') {
                    $lokasi = $response->json('city') . ', ' . $response->json('regionName') . ' (' . $response->json('country') . ')';
                }
            } catch (\Exception $e) {
                $lokasi = 'Lokasi tidak terdeteksi';
            }
        }

        LogKeuangan::create([
            'aksi'        => $aksi,
            'user_id'     => auth()->id() ?? 0,
            'nama_user'   => auth()->check() ? (auth()->user()->nama_lengkap ?? auth()->user()->username) : 'Guest/System',
            'role'        => auth()->check() ? auth()->user()->role : 'Unknown',
            'ip_address'  => $ip,
            'lokasi'      => $lokasi,
            'device_info' => request()->header('User-Agent'),
            'created_at'  => now(),
        ]);
    }
}
