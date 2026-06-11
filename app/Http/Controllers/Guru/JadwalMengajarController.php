<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\JadwalPelajaran;
use App\Models\TahunAjaran;
use App\Models\JamMaster;
use Illuminate\Support\Facades\Auth;

class JadwalMengajarController extends Controller
{
    public function index()
    {
        $id_guru = Auth::user()->guru->id ?? 0;
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();

        $query = JadwalPelajaran::with(['kelas.jurusan', 'mapel', 'guru', 'tahunAjaran'])
            ->where('id_guru', $id_guru);

        if ($tahunAktif) {
            $query->where('id_tahun_ajaran', $tahunAktif->id);
        }

        $query->orderByRaw('FIELD(hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu")')
              ->orderBy('jam_mulai', 'ASC');

        $jadwal = $query->get();
        
        $jamMaster = JamMaster::where('is_istirahat', 0)->orderBy('urutan', 'ASC')->get();
        // Or get all and render in UI. Let's send all so we can show break times if needed.
        $jamMasterAll = JamMaster::orderBy('urutan', 'ASC')->get();

        return Inertia::render('Guru/JadwalMengajar/Index', [
            'jadwal' => $jadwal,
            'tahun_aktif' => $tahunAktif,
            'jam_master' => $jamMasterAll
        ]);
    }
}
