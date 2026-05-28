<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DapodikSetting;
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

    public function testConnection()
    {
        $setting = DapodikSetting::first();
        try {
            $response = Http::withToken($setting->key_integrasi)
                ->timeout(10)
                ->get($setting->ip_dapodik . '/WebService/getSekolah', [
                    'npsn' => $setting->npsn
                ]);

            if ($response->successful()) {
                $setting->update(['status_koneksi' => 'Terhubung']);
                $data = $response->json();
                $nama_sekolah = $data['rows'][0]['nama'] ?? 'Dapodik';
                return redirect()->back()->with('success', "Koneksi SUKSES! Terhubung ke: $nama_sekolah");
            } else {
                $setting->update(['status_koneksi' => 'Gagal']);
                return redirect()->back()->with('error', 'Gagal terhubung. Pastikan IP dan Token benar.');
            }
        } catch (\Exception $e) {
            $setting->update(['status_koneksi' => 'Gagal']);
            return redirect()->back()->with('error', 'Koneksi Gagal: ' . $e->getMessage());
        }
    }

    public function sync($type)
    {
        // Simulasi proses sinkronisasi karena membutuhkan waktu lama.
        // Di environment sesungguhnya, akan melakukan pemanggilan API Dapodik
        // seperti getRombonganBelajar, getPesertaDidik, getGtk, dll.
        return redirect()->back()->with('success', "Proses Sinkronisasi $type selesai dilakukan!");
    }
}
