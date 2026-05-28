<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Statistik Utama
        $totalGuru = Guru::count();
        $totalSiswa = Siswa::count();
        $totalUser = User::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        // Hitung Role Spesifik (Wali Kelas)
        $totalWaliKelas = Kelas::whereNotNull('guru_id')->count();

        // Keuangan Dummy (bisa disesuaikan dengan tabel tagihan nanti)
        $totalBayar = DB::table('tbl_transaksi')->sum('jumlah_bayar') ?? 0;
        
        $totalTagihan = DB::table('tbl_tagihan')->sum('nominal_tagihan') ?? 0;
        $totalTerbayar = DB::table('tbl_tagihan')->sum('nominal_terbayar') ?? 0;
        $totalTunggak = $totalTagihan - $totalTerbayar;

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'guru' => $totalGuru,
                'siswa' => $totalSiswa,
                'user' => $totalUser,
                'kelas' => $totalKelas,
                'mapel' => $totalMapel,
                'wali_kelas' => $totalWaliKelas,
            ],
            'finance' => [
                'total_bayar' => (int) $totalBayar,
                'total_tunggakan' => (int) $totalTunggak,
            ],
            'chart' => [
                'labels' => ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"],
                'presensi' => [90, 85, 95, 92, 88, 0, 0],
            ]
        ]);
    }
}
