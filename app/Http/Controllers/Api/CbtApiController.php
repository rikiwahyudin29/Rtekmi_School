<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SoalData;
use App\Models\UjianSiswa;
use App\Models\JadwalUjian;

class CbtApiController extends Controller
{
    public function getSoal($ujian_id)
    {
        // Dalam implementasi nyata, ambil dari tabel draft/bank soal yang sesuai
        // serta jawaban sementara siswa jika ada.
        $ujian = UjianSiswa::with('jadwal.bankSoal')->findOrFail($ujian_id);
        
        $soal = SoalData::with('opsi')
            ->where('bank_id', $ujian->jadwal->id_bank_soal)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $soal
        ]);
    }

    public function saveJawaban(Request $request)
    {
        // Simpan jawaban siswa secara asinkron (realtime auto-save)
        // Logika simpan ke jawaban_pg_ujian_siswa dll.
        
        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban tersimpan'
        ]);
    }

    public function finish(Request $request, $ujian_id)
    {
        $ujian = UjianSiswa::findOrFail($ujian_id);
        $ujian->update([
            'status' => 2,
            'waktu_selesai' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ujian selesai'
        ]);
    }
}
