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

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->session()->get('role_active', 'admin'); // Default to admin for now if not set

        if ($role === 'admin' || $role === 'kepsek') {
            return $this->adminDashboard();
        } elseif ($role === 'guru') {
            return $this->guruDashboard();
        } elseif ($role === 'siswa') {
            return $this->siswaDashboard();
        }

        return Inertia::render('Dashboard'); // Fallback
    }

    private function adminDashboard()
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

        // Dummy Data untuk Chart Presensi (bisa diganti query db asli nanti)
        $labels = [];
        $presensi = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); 
            $presensi[] = rand(70, 100); // Dummy data for now
        }

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
                'labels' => $labels,
                'presensi' => $presensi,
            ]
        ]);
    }

    private function guruDashboard()
    {
        $guruId = auth()->user()->guru->id ?? null;
        
        $totalBankSoal = \App\Models\BankSoal::where('user_id', auth()->id())->count();
        $totalDraftUjian = \App\Models\DraftUjian::whereHas('bankSoal', function($q) {
            $q->where('user_id', auth()->id());
        })->count();
        $totalJadwalUjian = \App\Models\JadwalUjian::whereHas('draftUjian.bankSoal', function($q) {
            $q->where('user_id', auth()->id());
        })->count();

        // Dummy Data untuk Chart Presensi (bisa diganti query db asli nanti)
        $labels = [];
        $presensi = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); 
            $presensi[] = rand(85, 100); 
        }

        return Inertia::render('Guru/Dashboard', [
            'stats' => [
                'bank_soal' => $totalBankSoal,
                'draft_ujian' => $totalDraftUjian,
                'jadwal_ujian' => $totalJadwalUjian,
            ],
            'chart' => [
                'labels' => $labels,
                'presensi' => $presensi,
            ]
        ]);
    }

    private function siswaDashboard()
    {
        $siswa = auth()->user()->siswa ?? \App\Models\Siswa::where('user_id', auth()->id())->first();
        $siswaId = $siswa->id ?? null;
        
        $totalUjianDiikuti = \App\Models\UjianSiswa::where('siswa_id', $siswaId)->count();
        $ujianAktif = \App\Models\UjianSiswa::where('siswa_id', $siswaId)
            ->whereHas('jadwal', function($q) {
                $q->where('status_ujian', 'AKTIF');
            })->count();

        // Dummy Data untuk Chart Kehadiran (bisa diganti query db asli nanti)
        $labels = [];
        $kehadiran = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); 
            $kehadiran[] = rand(90, 100); 
        }

        return Inertia::render('Siswa/Dashboard', [
            'stats' => [
                'ujian_diikuti' => $totalUjianDiikuti,
                'ujian_aktif' => $ujianAktif,
                'tugas_belum' => 0, // Dummy
            ],
            'chart' => [
                'labels' => $labels,
                'kehadiran' => $kehadiran,
            ]
        ]);
    }
}
