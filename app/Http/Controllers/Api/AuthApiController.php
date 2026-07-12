<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('username')).'|'.$request->ip());
    }

    public function login(Request $request)
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            return response()->json([
                'status'  => false,
                'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam ' . ceil($seconds / 60) . ' menit.'
            ], 429);
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $device_id = $request->input('device_id');
        $device_name = $request->input('device_name');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (!$username || !$password) {
            return response()->json([
                'status'  => false,
                'message' => 'Username dan Password wajib diisi!'
            ], 400);
        }

        if (!$device_id) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal: Device ID wajib dikirim dari aplikasi.'
            ], 400);
        }

        // Cari user menggunakan Eloquent (agar bisa generate token)
        $user = \App\Models\User::where('username', $username)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey($request));
            return response()->json([
                'status'  => false,
                'message' => 'Username tidak terdaftar!'
            ], 404);
        }

        // Cek kecocokan password menggunakan Hash::check (kompatibel dengan password_hash PHP)
        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey($request));
            return response()->json([
                'status'  => false,
                'message' => 'Password yang dimasukkan salah!'
            ], 401);
        }

        // Cek status aktif (menggunakan kolom 'active' di tbl_users)
        if (isset($user->active) && $user->active == 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Akun dinonaktifkan. Silakan hubungi admin.'
            ], 403);
        }

        // DEVICE BINDING LOGIC
        if ($user->role !== 'admin') {
            $deviceCount = \App\Models\UserDevice::where('user_id', $user->id)->count();
            $existingDevice = \App\Models\UserDevice::where('user_id', $user->id)
                ->where('device_id', $device_id)->first();

            $maxDevice = $user->max_device ?? (($user->role === 'siswa') ? 1 : 2); // Cek max_device dari db, default Siswa 1, Guru 2

            if ($existingDevice) {
                // Perbarui waktu login terakhir dan lokasi
                $existingDevice->update([
                    'last_login_at' => now(),
                    'last_ip' => $request->ip(),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            } else {
                if ($deviceCount >= $maxDevice) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Gagal: Akun Anda sudah terikat di perangkat lain. Hubungi Admin untuk melakukan reset.'
                    ], 403);
                } else {
                    // Daftarkan perangkat baru beserta lokasinya
                    \App\Models\UserDevice::create([
                        'user_id' => $user->id,
                        'device_id' => $device_id,
                        'device_name' => $device_name,
                        'last_ip' => $request->ip(),
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'last_login_at' => now(),
                    ]);
                }
            }
        }

        // Tarik data spesifik siswa jika rolenya adalah siswa
        $detailSiswa = null;
        if ($user->role === 'siswa') {
            $detailSiswa = DB::table('tbl_siswa')->where('user_id', $user->id)->first();
        }

        // Generate Sanctum Token
        // Hapus token lama dari perangkat ini (opsional, agar tidak menumpuk)
        $user->tokens()->where('name', $device_id)->delete();
        $token = $user->createToken($device_id)->plainTextToken;

        RateLimiter::clear($this->throttleKey($request));

        $responseData = [
            'id_user'      => $user->id,
            'username'     => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'role'         => $user->role,
            'token'        => $token,
            'detail_siswa' => $detailSiswa
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil!',
            'data'    => $responseData
        ], 200);
    }
}
