<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (!$username || !$password) {
            return response()->json([
                'status'  => false,
                'message' => 'Username dan Password wajib diisi!'
            ], 400);
        }

        // Cari user berdasarkan username (bisa berupa NIS/NISN) di tbl_users
        $user = DB::table('tbl_users')->where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Username tidak terdaftar!'
            ], 404);
        }

        // Cek kecocokan password menggunakan Hash::check (kompatibel dengan password_hash PHP)
        if (!Hash::check($password, $user->password)) {
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

        // Tarik data spesifik siswa jika rolenya adalah siswa
        $detailSiswa = null;
        if ($user->role === 'siswa') {
            $detailSiswa = DB::table('tbl_siswa')->where('id_user', $user->id)->first();
        }

        $responseData = [
            'id_user'      => $user->id,
            'username'     => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'role'         => $user->role,
            'detail_siswa' => $detailSiswa
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil!',
            'data'    => $responseData
        ], 200);
    }
}
