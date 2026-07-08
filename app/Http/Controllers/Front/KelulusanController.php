<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KelulusanController extends Controller
{
    /**
     * Tampilan Halaman Utama Portal Kelulusan
     */
    public function index()
    {
        $setting = DB::table('tbl_setting_kelulusan')->where('id', 1)->first();
        $sekolah = DB::table('tbl_sekolah')->first();

        // Default setting if empty
        if (!$setting) {
            $setting = (object) ['tgl_pengumuman' => date('Y-m-d H:i:s', strtotime('+1 days'))];
        }

        return Inertia::render('Front/Kelulusan', [
            'web' => $sekolah,
            'setting' => $setting
        ]);
    }

    /**
     * Proses Cek NISN (AJAX/Inertia Request)
     */
    public function cek(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
            'tanggal_lahir' => 'required|date'
        ]);

        $nisn = trim($request->input('nisn'));
        $tanggal_lahir = $request->input('tanggal_lahir');

        // Cari siswa di kelas XII berdasarkan NISN dan Tanggal Lahir
        $siswa = DB::table('tbl_siswa')
            ->select('tbl_siswa.id', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_kelas.nama_kelas')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_siswa.nisn', $nisn)
            ->where('tbl_siswa.tanggal_lahir', $tanggal_lahir)
            ->where(function($query) {
                $query->where('tbl_kelas.nama_kelas', 'like', '%XII%')
                      ->orWhere('tbl_kelas.nama_kelas', 'like', '%12%');
            })
            ->first();

        if ($siswa) {
            // Daftarkan sesi
            session(['siswa_kelulusan_id' => $siswa->id]);
            return response()->json([
                'status' => 'success',
                'data' => $siswa
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan! Pastikan NISN dan Tanggal Lahir sesuai.'
            ], 404);
        }
    }

    /**
     * Ambil Hasil Kelulusan (Setelah Loading Animasi)
     */
    public function getHasil()
    {
        $siswaId = session('siswa_kelulusan_id');
        if (!$siswaId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi berakhir, silakan refresh dan cek ulang NISN Anda.'
            ], 401);
        }

        $kelulusan = DB::table('tbl_kelulusan')->where('siswa_id', $siswaId)->first();
        $siswa = DB::table('tbl_siswa')->select('nama_lengkap', 'nisn')->where('id', $siswaId)->first();

        $statusLulus = $kelulusan?->status_lulus ?? 'Pending';
        $catatan = $kelulusan?->catatan ?? '';

        return response()->json([
            'status' => 'success',
            'siswa' => $siswa,
            'status_lulus' => $statusLulus,
            'catatan' => $catatan
        ]);
    }

    /**
     * Download Dokumen SKL / Transkrip
     */
    public function downloadDokumen(Request $request)
    {
        $siswaId = session('siswa_kelulusan_id');
        if (!$siswaId) {
            return abort(403, "Sesi berakhir atau akses ilegal. Silakan kembali ke portal utama.");
        }

        $tipe = $request->input('tipe');

        // Verifikasi kelulusan
        $kelulusan = DB::table('tbl_kelulusan')->where('siswa_id', $siswaId)->first();
        if (strtoupper($kelulusan?->status_lulus ?? '') !== 'LULUS') {
            return abort(403, "Akses Ditolak! Dokumen ditangguhkan karena status Anda belum Lulus.");
        }

        // Panggil metode cetak dari Admin Kelulusan Controller untuk generate tampilan HTML (Native Print)
        $adminKelulusan = app(\App\Http\Controllers\Admin\KelulusanController::class);

        if ($tipe === 'skl') {
            return $adminKelulusan->cetakSkl($siswaId);
        } elseif ($tipe === 'transkrip') {
            return $adminKelulusan->cetakTranskrip($siswaId);
        } else {
            return abort(404, "Parameter dokumen tidak dikenali oleh sistem.");
        }
    }
}
