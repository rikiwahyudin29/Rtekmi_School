<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Kelas;
use App\Models\Siswa;

class MonitoringRaporController extends Controller
{
    public function index(Request $request)
    {
        $kelas_id = $request->input('kelas_id');
        $kelas_list = Kelas::all();
        
        $siswa = [];
        if ($kelas_id) {
            $siswa = Siswa::with(['raporAkhir', 'raporKehadiran', 'raporCatatanWali'])->where('kelas_id', $kelas_id)->get();
        }

        return Inertia::render('Admin/Monitoring/Rapor', [
            'kelas_list' => $kelas_list,
            'siswa' => $siswa,
            'filters' => $request->only(['kelas_id'])
        ]);
    }
}
