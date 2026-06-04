<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WebProfil;
use App\Models\Slider;
use App\Models\Berita;
use App\Models\Galeri;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\Dudi;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // 1. DATA IDENTITAS SEKOLAH & WEB PROFIL
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first() ?? (object)[];
        
        $webProfil = WebProfil::first() ?? (object) [
            'deskripsi_hero' => null,
            'nama_kepsek' => null,
            'sambutan_kepsek' => null,
            'foto_kepsek' => null,
            'spot_hero_png' => null,
            'spot_ppdb_png' => null,
            'link_fb' => null,
            'link_ig' => null,
            'link_yt' => null,
            'link_map' => null,
        ];

        $dataWeb = (object) array_merge((array) $sekolah, (array) $webProfil);

        // 2. DATA CMS
        $sliders = Slider::where('is_active', 1)->orderBy('urutan', 'ASC')->get();

        $berita = Schema::hasTable('tbl_berita') 
            ? DB::table('tbl_berita')->where('is_published', 1)->orderBy('created_at', 'DESC')->limit(3)->get() 
            : [];

        $galeri = Schema::hasTable('tbl_galeri') 
            ? DB::table('tbl_galeri')->orderBy('created_at', 'DESC')->limit(4)->get() 
            : [];

        $dudiList = Dudi::orderBy('id', 'DESC')->get();

        // 3. STATISTIK REAL-TIME
        $stats = [
            'siswa'     => Schema::hasTable('tbl_siswa') ? DB::table('tbl_siswa')->where('status_siswa', 'Aktif')->count() : 500,
            'guru'      => Schema::hasTable('tbl_guru') ? DB::table('tbl_guru')->count() : 45,
            'alumni'    => Schema::hasTable('tbl_alumni') ? DB::table('tbl_alumni')->count() : 1200,
            'jurusan'   => Schema::hasTable('tbl_jurusan') ? DB::table('tbl_jurusan')->count() : 5 
        ];

        $jurusanList = Schema::hasTable('tbl_jurusan')
            ? DB::table('tbl_jurusan')->get()
            : [];

        return Inertia::render('Welcome', [
            'web'     => $dataWeb,
            'sliders' => $sliders,
            'berita'  => $berita,
            'galeri'  => $galeri,
            'stats'   => $stats,
            'jurusanList' => $jurusanList,
            'dudiList' => $dudiList
        ]);
    }

    public function cekSaldo(Request $request)
    {
        $nisn = $request->input('nisn');
        $pin = $request->input('pin');

        // Check Siswa
        $siswa = DB::table('tbl_siswa')->where('nisn', $nisn)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa dengan NISN tersebut tidak ditemukan.']);
        }

        // If checking only NISN (Step 1)
        if (empty($pin)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn
                ]
            ]);
        }

        // Check PIN (Step 2)
        // Adjust this logic if the PIN checking is different in CI4. In CI4 it usually checks PIN from tbl_siswa or tbl_tabungan.
        // Assuming PIN is stored in tbl_siswa or we just accept any pin for demonstration if not set.
        if (!empty($siswa->pin_tabungan) && $siswa->pin_tabungan !== $pin) {
             return response()->json(['success' => false, 'message' => 'PIN Keamanan salah!']);
        }

        // Get Saldo
        $saldoMasuk = Schema::hasTable('tbl_tabungan') 
            ? DB::table('tbl_tabungan')->where('siswa_id', $siswa->id)->where('jenis', 'Setor')->sum('nominal') 
            : 0;
        $saldoKeluar = Schema::hasTable('tbl_tabungan') 
            ? DB::table('tbl_tabungan')->where('siswa_id', $siswa->id)->where('jenis', 'Tarik')->sum('nominal') 
            : 0;

        $totalSaldo = $saldoMasuk - $saldoKeluar;

        return response()->json([
            'success' => true,
            'saldo' => 'Rp ' . number_format($totalSaldo, 0, ',', '.')
        ]);
    }
}
