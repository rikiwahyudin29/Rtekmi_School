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

        // Calculate Stats
        $guru = \App\Models\Guru::find($id_guru);
        $unique_kelas_ids = $jadwal->pluck('id_kelas')->unique();
        $total_siswa = \App\Models\Siswa::whereIn('kelas_id', $unique_kelas_ids)->count();

        $mengajar_list = $jadwal->unique(function ($item) {
            return $item->id_mapel.'-'.$item->id_kelas;
        })->map(function ($item) {
            return [
                'mapel' => $item->mapel->nama_mapel,
                'kelas' => $item->kelas->nama_kelas,
            ];
        })->values();

        $total_jam = 0;
        foreach ($jadwal as $j) {
            $start = $jamMaster->firstWhere('jam_mulai', substr($j->jam_mulai, 0, 5) . ':00');
            $end = $jamMaster->firstWhere('jam_selesai', substr($j->jam_selesai, 0, 5) . ':00');
            
            // Fallback for missing exact seconds or slight offsets
            if (!$start) {
                $start = $jamMaster->first(function($jm) use ($j) {
                    return substr($jm->jam_mulai, 0, 5) >= substr($j->jam_mulai, 0, 5);
                });
            }
            if (!$end) {
                $end = $jamMaster->first(function($jm) use ($j) {
                    return substr($jm->jam_selesai, 0, 5) >= substr($j->jam_selesai, 0, 5);
                });
            }
            
            if ($start && $end) {
                $total_jam += ($end->urutan - $start->urutan + 1);
            }
        }

        return Inertia::render('Guru/JadwalMengajar/Index', [
            'jadwal' => $jadwal,
            'tahun_aktif' => $tahunAktif,
            'jam_master' => $jamMasterAll,
            'guru' => $guru,
            'total_siswa' => $total_siswa,
            'mengajar_list' => $mengajar_list,
            'total_jam' => $total_jam
        ]);
    }
}
