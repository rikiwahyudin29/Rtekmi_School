<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\RaporAkhir;
use App\Models\RaporKehadiran;
use App\Models\RaporCatatanWali;
use App\Models\RaporPkl;

class RaporController extends Controller
{
    public function index()
    {
        // Mendapatkan ID siswa dari auth user
        $siswa_id = Auth::user()->siswa->id ?? 1; // Simulasi

        $rapor_akhir = RaporAkhir::with('mapel')->where('siswa_id', $siswa_id)->where('semester', 1)->get();
        $kehadiran = RaporKehadiran::where('siswa_id', $siswa_id)->where('semester', 1)->first();
        $catatan = RaporCatatanWali::where('siswa_id', $siswa_id)->where('semester', 1)->first();
        $pkl = RaporPkl::with('dudi')->where('siswa_id', $siswa_id)->where('semester', 1)->get();

        return Inertia::render('Siswa/Rapor/Index', [
            'rapor_akhir' => $rapor_akhir,
            'kehadiran' => $kehadiran,
            'catatan' => $catatan,
            'pkl' => $pkl
        ]);
    }
}
