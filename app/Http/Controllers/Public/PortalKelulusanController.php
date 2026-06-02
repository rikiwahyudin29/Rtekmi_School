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

    public function requestAntrian(Request $request)
    {
        $siswaId = session('siswa_kelulusan_id');
        $tipe = $request->query('tipe');

        if (!$siswaId) return response()->json(['status' => 'error', 'msg' => 'Sesi habis, silakan login ulang.']);

        // Hapus antrian lama milik siswa ini
        \App\Models\AntrianDownload::where('siswa_id', $siswaId)->where('tipe', $tipe)->delete();

        // Masukkan ke daftar antrian baru
        $antrian = \App\Models\AntrianDownload::create([
            'siswa_id' => $siswaId,
            'tipe' => $tipe,
            'status' => 'antri'
        ]);
        
        return response()->json(['status' => 'success', 'id_antrian' => $antrian->id]);
    }

    public function cekAntrian($idAntrian)
    {
        // 1. SAPU BERSIH: Hapus semua antrian yang umurnya lebih dari 4 menit
        \App\Models\AntrianDownload::where('created_at', '<', now()->subMinutes(4))->delete();

        // 2. Cek status antrian milik siswa ini
        $saya = \App\Models\AntrianDownload::find($idAntrian);
        
        // Jika data hilang/disapu bersih ATAU statusnya proses, berarti siap didownload
        if (!$saya || $saya->status == 'proses') return response()->json(['status' => 'ready']); 

        // 3. Cek jumlah CPU yang sedang kerja (Max 2 PDF bersamaan untuk Browsershot)
        $sedangProses = \App\Models\AntrianDownload::where('status', 'proses')->count();

        if ($sedangProses < 2) {
            // Cari siapa yang antri paling depan
            $palingDepan = \App\Models\AntrianDownload::where('status', 'antri')->orderBy('id', 'ASC')->first();

            // Jika siswa ini adalah yang paling depan, gas proses!
            if ($palingDepan && $palingDepan->id == $idAntrian) {
                $saya->update(['status' => 'proses', 'created_at' => now()]);
                return response()->json(['status' => 'ready']);
            }
        }

        // 4. Jika giliran belum tiba, hitung sisa antrian di depannya
        $sisaAntrian = \App\Models\AntrianDownload::where('status', 'antri')
                             ->where('id', '<=', $idAntrian)
                             ->count();

        return response()->json(['status' => 'antri', 'sisa' => $sisaAntrian]);
    }

    public function downloadDokumen(Request $request)
    {
        $siswaId = session('siswa_kelulusan_id');
        if (!$siswaId) {
            return "Sesi berakhir atau akses ilegal. Silakan login kembali melalui portal utama.";
        }

        $tipe = $request->query('tipe');

        $kelulusan = Kelulusan::where('siswa_id', $siswaId)->first();
        $siswa = Siswa::find($siswaId);
        $statusLulus = $kelulusan ? strtoupper($kelulusan->status_lulus) : 'PENDING';

        if ($statusLulus !== 'LULUS' && $statusLulus !== 'LULUS BERSYARAT') {
            return "Akses Ditolak! Dokumen ditangguhkan karena status Anda belum Lulus.";
        }

        $adminKelulusan = new KelulusanController();
        
        if ($tipe === 'skl') {
            $view = $adminKelulusan->cetakSkl($siswaId);
        } elseif ($tipe === 'transkrip') {
            $view = $adminKelulusan->cetakTranskrip($siswaId);
        } else {
            return "Parameter dokumen tidak dikenali oleh sistem.";
        }

        try {
            $html = $view->render();

            // Hapus tiket antrian karena sudah masuk ke proses render
            \App\Models\AntrianDownload::where('siswa_id', $siswaId)->where('tipe', $tipe)->delete();

            $pdf = \Spatie\Browsershot\Browsershot::html($html)
                ->paperSize(215.9, 330.2)
                ->showBackground()
                ->noSandbox()
                ->pdf();
                
            $filename = strtoupper($tipe) . '_' . str_replace(' ', '_', $siswa->nama_lengkap) . '.pdf';

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return "Terjadi kesalahan saat memproses PDF: " . $e->getMessage() . "<br><br>Harap pastikan library spatie/browsershot dan puppeteer sudah terinstall di server.";
        }
    }
}
