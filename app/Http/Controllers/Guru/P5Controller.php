<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\P5Kelompok;
use App\Models\P5Nilai;
use App\Models\P5SubElemen;
use App\Models\Siswa;

class P5Controller extends Controller
{
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? 1; // Simulasi
        
        $kelompok = P5Kelompok::with(['projek.tema'])->where('guru_koordinator_id', $guru_id)->get();

        return Inertia::render('Guru/P5/Index', [
            'kelompok' => $kelompok
        ]);
    }

    public function inputNilai($id)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $kelompok = P5Kelompok::with(['projek.tema'])->where('id', $id)->where('guru_koordinator_id', $guru_id)->firstOrFail();
        
        // Ambil list siswa berdasarkan kelas yang diassign ke kelompok ini
        $kelas_ids = explode(',', $kelompok->kelas_id_list);
        $siswa = Siswa::whereIn('kelas_id', $kelas_ids)->get();

        // Ambil elemen/sub elemen
        $subElemen = P5SubElemen::all(); // Idealnya di filter berdasarkan dimensi yang dipilih di projek
        
        $nilai = P5Nilai::where('kelompok_id', $kelompok->id)->get();

        return Inertia::render('Guru/P5/InputNilai', [
            'kelompok' => $kelompok,
            'siswa' => $siswa,
            'subElemen' => $subElemen,
            'nilai_existing' => $nilai
        ]);
    }

    public function storeNilai(Request $request, $id)
    {
        $request->validate([
            'nilai_data' => 'required|array'
        ]);

        foreach ($request->nilai_data as $data) {
            P5Nilai::updateOrCreate(
                [
                    'kelompok_id' => $id,
                    'siswa_id' => $data['siswa_id'],
                    'sub_elemen_id' => $data['sub_elemen_id'],
                ],
                [
                    'nilai' => $data['nilai']
                ]
            );
        }

        return redirect()->back()->with('success', 'Nilai P5 berhasil disimpan');
    }
}
