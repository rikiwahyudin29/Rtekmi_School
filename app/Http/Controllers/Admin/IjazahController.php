<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\IjazahSiswa;
use App\Models\IjazahNilai;

class IjazahController extends Controller
{
    public function index()
    {
        // Khusus kelas 12
        $kelas = Kelas::where('tingkat', 12)->get();
        $selected_kelas_id = request('kelas_id', $kelas->first()->id ?? null);
        
        $siswa = [];
        $ijazah_data = [];
        
        if ($selected_kelas_id) {
            $siswa = Siswa::where('kelas_id', $selected_kelas_id)->get();
            $ijazah_data = IjazahSiswa::whereIn('siswa_id', $siswa->pluck('id'))->get()->keyBy('siswa_id');
        }

        return Inertia::render('Admin/Ijazah/Index', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'ijazah_data' => $ijazah_data,
            'selected_kelas_id' => $selected_kelas_id
        ]);
    }

    public function storeDataIjazah(Request $request)
    {
        $request->validate([
            'data' => 'required|array'
        ]);

        foreach ($request->data as $siswa_id => $d) {
            if (isset($d['no_ijazah'])) {
                IjazahSiswa::updateOrCreate(
                    ['siswa_id' => $siswa_id],
                    [
                        'no_ijazah' => $d['no_ijazah'],
                        'tanggal_lulus' => $d['tanggal_lulus'] ?? null,
                        'keterangan' => $d['keterangan'] ?? null
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Data Ijazah berhasil disimpan.');
    }
}
