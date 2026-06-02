<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Siswa;
use App\Models\SettingKelulusan;
use App\Models\Sekolah;
use App\Models\Kelulusan;
use App\Http\Controllers\Admin\KelulusanController;

class PortalKelulusanController extends Controller
{
    public function index()
    {
        $setting = SettingKelulusan::first();
        $sekolah = Sekolah::first();

        // If setting is empty, create default for tomorrow
        if (!$setting) {
            $setting = (object) ['tgl_pengumuman' => date('Y-m-d H:i:s', strtotime('+1 days'))];
        }

        return Inertia::render('Public/Kelulusan/Index', [
            'setting' => $setting,
            'sekolah' => $sekolah,
            'asset_url' => asset('')
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'nisn' => 'required'
        ]);

        $nisn = trim($request->nisn);

        $siswa = Siswa::with('kelas')->where('nisn', $nisn)
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'LIKE', '%XII%')
                  ->orWhere('nama_kelas', 'LIKE', '%12%')
                  ->orWhere('tingkat', 12);
            })->first();

        if ($siswa) {
            session(['siswa_kelulusan_id' => $siswa->id]);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $siswa->id,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn,
                    'nama_kelas' => $siswa->kelas->nama_kelas ?? '-'
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'NISN tidak terdaftar sebagai Siswa Kelas XII.'
        ]);
    }

    public function getHasil(Request $request)
    {
        $siswaId = session('siswa_kelulusan_id');
        if (!$siswaId) {
            return response()->json(['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
        }

        $kelulusan = Kelulusan::where('siswa_id', $siswaId)->first();
        $siswa = Siswa::find($siswaId);

        $statusLulus = $kelulusan ? $kelulusan->status_lulus : 'Pending';
        $catatan = $kelulusan ? $kelulusan->catatan : '';

        return response()->json([
            'status' => 'success',
            'siswa' => [
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn
            ],
            'status_lulus' => $statusLulus,
            'catatan' => $catatan
        ]);
    }

    public function downloadDokumen(Request $request)
    {
        $siswaId = session('siswa_kelulusan_id');
        if (!$siswaId) {
            return "Sesi berakhir atau akses ilegal. Silakan login kembali melalui portal utama.";
        }

        $tipe = $request->query('tipe');

        $kelulusan = Kelulusan::where('siswa_id', $siswaId)->first();
        $statusLulus = $kelulusan ? strtoupper($kelulusan->status_lulus) : 'PENDING';

        if ($statusLulus !== 'LULUS' && $statusLulus !== 'LULUS BERSYARAT') {
            return "Akses Ditolak! Dokumen ditangguhkan karena status Anda belum Lulus.";
        }

        $adminKelulusan = new KelulusanController();
        
        if ($tipe === 'skl') {
            return $adminKelulusan->cetakSkl($siswaId);
        } elseif ($tipe === 'transkrip') {
            return $adminKelulusan->cetakTranskrip($siswaId);
        }

        return "Parameter dokumen tidak dikenali oleh sistem.";
    }
}
