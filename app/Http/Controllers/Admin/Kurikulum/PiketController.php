<?php

namespace App\Http\Controllers\Admin\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PiketController extends Controller
{
    // =====================================
    // 1. PENJADWALAN PIKET
    // =====================================
    public function jadwal()
    {
        $guru = DB::table('tbl_guru')->orderBy('nama_lengkap', 'ASC')->get();
        $jadwal = DB::table('tbl_jadwal_piket')
            ->select('tbl_jadwal_piket.*', 'tbl_guru.nama_lengkap')
            ->join('tbl_guru', 'tbl_guru.id', '=', 'tbl_jadwal_piket.guru_id')
            ->get();

        // Kelompokkan berdasarkan hari
        $jadwalPerHari = [
            'Senin' => [], 'Selasa' => [], 'Rabu' => [], 
            'Kamis' => [], 'Jumat' => [], 'Sabtu' => [], 'Minggu' => []
        ];

        foreach ($jadwal as $j) {
            $jadwalPerHari[$j->hari][] = $j;
        }

        return Inertia::render('Admin/Kurikulum/Piket/Jadwal', [
            'guru' => $guru,
            'jadwal_per_hari' => $jadwalPerHari
        ]);
    }

    public function simpanJadwal(Request $request)
    {
        $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'guru_id' => 'required|array',
            'guru_id.*' => 'integer'
        ]);

        $added = 0;
        foreach ($request->guru_id as $g_id) {
            // Cek apakah guru sudah piket di hari yang sama
            $exists = DB::table('tbl_jadwal_piket')
                ->where('hari', $request->hari)
                ->where('guru_id', $g_id)
                ->exists();

            if (!$exists) {
                DB::table('tbl_jadwal_piket')->insert([
                    'hari' => $request->hari,
                    'guru_id' => $g_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $added++;
            }
        }

        if ($added > 0) {
            return back()->with('message', $added . ' Guru berhasil ditambahkan ke jadwal hari ' . $request->hari . '!');
        } else {
            return back()->with('error', 'Semua guru yang dipilih sudah terdaftar di hari ' . $request->hari);
        }
    }

    public function hapusJadwal($id)
    {
        DB::table('tbl_jadwal_piket')->where('id', $id)->delete();
        return back()->with('message', 'Jadwal piket berhasil dihapus!');
    }

    // =====================================
    // 2. REKAP / MONITORING PIKET (DASHBOARD)
    // =====================================
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        // Rekap Jurnal Piket Hari Tersebut
        $jurnal = DB::table('tbl_jurnal_piket')
            ->select('tbl_jurnal_piket.*', 'g1.nama_lengkap as nama_guru', 'g2.nama_lengkap as nama_pengganti')
            ->join('tbl_guru as g1', 'g1.id', '=', 'tbl_jurnal_piket.guru_id')
            ->leftJoin('tbl_guru as g2', 'g2.id', '=', 'tbl_jurnal_piket.guru_pengganti_id')
            ->where('tanggal', $tanggal)
            ->get();

        // Rekap Buku Tamu Hari Tersebut
        $buku_tamu = DB::table('tbl_buku_tamu')
            ->select('tbl_buku_tamu.*', 'tbl_guru.nama_lengkap as nama_pencatat')
            ->leftJoin('tbl_guru', 'tbl_guru.id', '=', 'tbl_buku_tamu.pencatat_id')
            ->where('tanggal', $tanggal)
            ->orderBy('jam_datang', 'DESC')
            ->get();

        // Rekap Izin Keluar Siswa Hari Tersebut
        // Note: tbl_izin_keluar pencatat_id is currently user_id. Let's join users.
        $izin_keluar = DB::table('tbl_izin_keluar')
            ->select('tbl_izin_keluar.*', 'tbl_siswa.nama_lengkap', 'tbl_kelas.nama_kelas', 'users.name as nama_pencatat')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_izin_keluar.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->leftJoin('users', 'users.id', '=', 'tbl_izin_keluar.pencatat_id')
            ->whereDate('waktu_keluar', $tanggal)
            ->orderBy('waktu_keluar', 'DESC')
            ->get();

        return Inertia::render('Admin/Kurikulum/Piket/Index', [
            'tanggal' => $tanggal,
            'jurnal' => $jurnal,
            'buku_tamu' => $buku_tamu,
            'izin_keluar' => $izin_keluar
        ]);
    }
}
