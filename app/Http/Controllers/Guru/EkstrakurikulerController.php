<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\EkskulNilai;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? 1; // Simulasi
        
        // Ambil ekskul yang dibina oleh guru ini
        $ekskuls = Ekskul::where('pembina_id', $guru_id)->get();
        
        // Default select first ekskul if multiple
        $selected_ekskul_id = request('ekskul_id', $ekskuls->first()->id ?? null);
        
        $anggota = [];
        $nilai_existing = [];
        
        if ($selected_ekskul_id) {
            $anggota = EkskulAnggota::with('siswa.kelas')->where('ekskul_id', $selected_ekskul_id)->get();
            $nilai_existing = EkskulNilai::where('ekskul_id', $selected_ekskul_id)
                ->where('semester', 1) // Simulasi ganjil
                ->get();
        }

        return Inertia::render('Guru/Ekskul/Index', [
            'ekskuls' => $ekskuls,
            'selected_ekskul_id' => $selected_ekskul_id,
            'anggota' => $anggota,
            'nilai_existing' => $nilai_existing
        ]);
    }

    public function storeNilai(Request $request)
    {
        $request->validate([
            'ekskul_id' => 'required|integer',
            'nilai_data' => 'required|array'
        ]);

        foreach ($request->nilai_data as $data) {
            EkskulNilai::updateOrCreate(
                [
                    'ekskul_id' => $request->ekskul_id,
                    'siswa_id' => $data['siswa_id'],
                    'semester' => 1 // Simulasi
                ],
                [
                    'nilai_huruf' => $data['nilai_huruf'],
                    'deskripsi_dapodik' => $data['deskripsi'] ?? null
                ]
            );
        }

        return redirect()->back()->with('success', 'Nilai Ekstrakurikuler berhasil disimpan');
    }
}
