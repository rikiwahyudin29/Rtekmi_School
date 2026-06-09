<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\KokuKelompok;
use App\Models\KokuTema;
use App\Models\KokuNilai;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;

class KokurikulerController extends Controller
{
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $kelompok = KokuKelompok::where('guru_id', $guru_id)->get();
        $tema = KokuTema::with('kegiatan')->get();
        
        $selected_kelompok_id = request('kelompok_id', $kelompok->first()->id ?? null);
        $siswa = [];
        $nilai = [];
        
        if ($selected_kelompok_id) {
            // Asumsi: Siswa di-assign ke kelompok (untuk simplifikasi kita ambil semua siswa sementara atau dummy)
            $siswa = Siswa::take(10)->get(); // Seharusnya berdasarkan KokuKelompokSiswa
            $nilai_data = KokuNilai::whereIn('siswa_id', $siswa->pluck('id'))->get();
            
            // Format nilai: nilai[siswa_id][kegiatan_id] = nilai
            foreach ($nilai_data as $n) {
                $nilai[$n->siswa_id][$n->kegiatan_id] = $n->nilai;
            }
        }

        return Inertia::render('Guru/Kokurikuler/Index', [
            'kelompok' => $kelompok,
            'tema' => $tema,
            'siswa' => $siswa,
            'nilai' => $nilai,
            'selected_kelompok_id' => $selected_kelompok_id
        ]);
    }

    public function storeNilai(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $kegiatans) {
            foreach ($kegiatans as $kegiatan_id => $nilai) {
                KokuNilai::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'kegiatan_id' => $kegiatan_id],
                    ['nilai' => $nilai]
                );
            }
        }

        return redirect()->back()->with('success', 'Nilai Kokurikuler berhasil disimpan.');
    }
}
