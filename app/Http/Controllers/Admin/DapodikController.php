<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DapodikSetting;
use App\Services\DapodikService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DapodikController extends Controller
{
    /**
     * Halaman Pengaturan Dapodik & Sinkronisasi
     */
    public function index()
    {
        $setting = DapodikSetting::first() ?? new DapodikSetting();
        return Inertia::render('Admin/Dapodik/Index', [
            'setting' => $setting
        ]);
    }

    /**
     * Menyimpan Konfigurasi Koneksi Web Service Dapodik
     */
    public function updateSetting(Request $request)
    {
        $request->validate([
            'npsn' => 'required|string',
            'ip_dapodik' => 'required|url',
            'key_integrasi' => 'required|string',
        ]);

        $setting = DapodikSetting::first();
        if (!$setting) {
            $setting = new DapodikSetting();
        }

        $setting->npsn = $request->npsn;
        $setting->ip_dapodik = rtrim($request->ip_dapodik, '/');
        $setting->key_integrasi = $request->key_integrasi;
        $setting->save();

        return redirect()->back()->with('success', 'Konfigurasi Dapodik berhasil disimpan.');
    }

    /**
     * Menguji Koneksi ke Web Service Dapodik
     */
    public function testKoneksi(DapodikService $dapodikService)
    {
        $setting = DapodikSetting::first();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi belum diset.']);
        }

        $isConnected = $dapodikService->testConnection();

        $setting->status_koneksi = $isConnected ? 'Terhubung' : 'Gagal';
        $setting->save();

        if ($isConnected) {
            return response()->json(['success' => true, 'message' => 'Berhasil terhubung ke Web Service Dapodik.']);
        }

        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke Web Service Dapodik. Pastikan IP dan Key Integrasi benar, serta aplikasi Dapodik dalam kondisi aktif.']);
    }

    /**
     * Endpoint untuk Tarik Data dari Dapodik (Contoh Tarik Siswa)
     * Untuk full integrasi, Anda bisa menambahkan fungsi tarik Sekolah, Guru, Rombel, Mapel
     */
    public function tarikSiswa(DapodikService $dapodikService)
    {
        try {
            // Memanggil endpoint Web Service Dapodik
            // 'getPesertaDidik' adalah nama endpoint standar Dapodik
            $siswaDapodik = $dapodikService->get('getPesertaDidik');

            $count = 0;
            // Di sini kita bisa melakukan mapping dan insert/update ke `tbl_siswa` lokal
            // foreach ($siswaDapodik as $sd) {
            //     // Contoh insert/update logika...
            //     $count++;
            // }

            return redirect()->back()->with('success', "Berhasil menarik $count data siswa dari Dapodik. (Note: Logika insert perlu disesuaikan dengan skema lokal)");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Endpoint untuk Kirim Nilai Rapor ke Dapodik
     */
    public function kirimNilai(DapodikService $dapodikService)
    {
        // Proses untuk membaca Nilai Akhir dari RaporAkhir dan mem-POST ke Dapodik.
        // Hal ini membutuhkan skema JSON spesifik sesuai dengan struktur web service Dapodik.
        return redirect()->back()->with('info', 'Fitur kirim nilai ke Dapodik dalam tahap pengembangan. Membutuhkan format JSON yang spesifik.');
    }
}
