<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DapodikSetting;
use App\Services\DapodikService;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class DapodikController extends Controller
{
    public function index()
    {
        $setting = DapodikSetting::first();
        if (!$setting) {
            $setting = DapodikSetting::create([
                'ip_dapodik' => 'http://localhost:5774',
                'key_integrasi' => '',
                'npsn' => '',
                'status_koneksi' => 'Gagal'
            ]);
        }
        return Inertia::render('Admin/Master/Dapodik/Index', [
            'setting' => $setting
        ]);
    }

    public function update(Request $request)
    {
        $setting = DapodikSetting::first();
        $setting->update([
            'ip_dapodik' => rtrim($request->ip_dapodik ?? 'http://localhost:5774', '/'),
            'key_integrasi' => $request->key_integrasi ?? '',
            'npsn' => $request->npsn ?? ''
        ]);
        return redirect()->back()->with('success', 'Pengaturan Dapodik berhasil disimpan.');
    }

    public function testConnection(DapodikService $dapodikService)
    {
        $setting = DapodikSetting::first();
        try {
            $isConnected = $dapodikService->testConnection();

            if ($isConnected) {
                $setting->update(['status_koneksi' => 'Terhubung']);
                return redirect()->back()->with('success', "Koneksi SUKSES! Terhubung ke Web Service Dapodik.");
            } else {
                $setting->update(['status_koneksi' => 'Gagal']);
                return redirect()->back()->with('error', 'Gagal terhubung. Pastikan IP dan Token benar, serta aplikasi Dapodik aktif.');
            }
        } catch (\Exception $e) {
            $setting->update(['status_koneksi' => 'Gagal']);
            return redirect()->back()->with('error', 'Koneksi Gagal: ' . $e->getMessage());
        }
    }

    public function sync($type, DapodikService $dapodikService)
    {
        try {
            $count = 0;
            if ($type == 'sekolah') {
                $data = $dapodikService->get('getSekolah');
                $count = count($data);
                // Lakukan pembaruan profil sekolah (tbl_sekolah)
            } elseif ($type == 'guru') {
                $data = $dapodikService->get('getGtk');
                $count = count($data);
                // Lakukan insert/update tbl_guru berdasarkan nuptk/nik
            } elseif ($type == 'siswa') {
                $data = $dapodikService->get('getPesertaDidik');
                $count = count($data);
                // Lakukan insert/update tbl_siswa berdasarkan nisn/nik
            } elseif ($type == 'rombel') {
                $data = $dapodikService->get('getRombonganBelajar');
                $count = count($data);
                // Lakukan sinkronisasi tbl_kelas
            } elseif ($type == 'mapel') {
                $data = $dapodikService->get('getMataPelajaran');
                $count = count($data);
                // Lakukan sinkronisasi tbl_mapel
            }

            return redirect()->back()->with('success', "Proses Tarik Data $type selesai! Berhasil mengambil $count baris data dari Dapodik.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal melakukan sinkronisasi $type: " . $e->getMessage());
        }
    }
}
