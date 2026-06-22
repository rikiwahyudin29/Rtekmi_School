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
use App\Models\TahunAjaran;

class RaporController extends Controller
{
    public function index()
    {
        // Mendapatkan ID siswa dari auth user
        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Sesi siswa tidak ditemukan.');
        }
        $siswa_id = $siswa->id;

        $semua_tahun = TahunAjaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        
        $selected_ta_id = request('tahun_ajaran_id', $tahun_ajaran_aktif ? $tahun_ajaran_aktif->id : null);
        $selected_ta = TahunAjaran::find($selected_ta_id);
        
        $semester_int = ($selected_ta && $selected_ta->semester === 'Genap') ? 2 : 1;

        if ($selected_ta && $selected_ta->status === 'Aktif') {
            $jadwal_pelajaran = \App\Models\JadwalPelajaran::with(['mapel', 'guru'])
                ->where('id_kelas', $siswa->kelas_id)
                ->get()
                ->unique('id_mapel')
                ->values();
        } else {
            // For past semesters, reconstruct the subjects from their RaporAkhir records
            $rapor_history = RaporAkhir::with(['mapel', 'guru'])
                ->where('siswa_id', $siswa_id)
                ->where('tahun_ajaran_id', $selected_ta_id)
                ->where('semester', $semester_int)
                ->get();
                
            $jadwal_pelajaran = $rapor_history->map(function($r) {
                return (object)[
                    'id_mapel' => $r->mapel_id,
                    'mapel' => $r->mapel,
                    'guru' => $r->guru
                ];
            })->unique('id_mapel')->values();
        }

        $rapor_akhir = RaporAkhir::with('mapel')
            ->where('siswa_id', $siswa_id)
            ->where('tahun_ajaran_id', $selected_ta_id)
            ->where('semester', $semester_int)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->keyBy('mapel_id');

        $formatif = \App\Models\NilaiFormatif::where('siswa_id', $siswa_id)
            ->where('tahun_ajaran_id', $selected_ta_id)
            ->join('tbl_tujuan_pembelajaran', 'tbl_nilai_formatif.tp_id', '=', 'tbl_tujuan_pembelajaran.id')
            ->select('tbl_nilai_formatif.*', 'tbl_tujuan_pembelajaran.kode_tp', 'tbl_tujuan_pembelajaran.deskripsi as tp_deskripsi')
            ->get()
            ->groupBy('mapel_id');
            
        $sumatif = \App\Models\NilaiSumatif::where('siswa_id', $siswa_id)
            ->where('tahun_ajaran_id', $selected_ta_id)
            ->where('semester', $semester_int)
            ->get()
            ->groupBy('mapel_id');

        $kehadiran = RaporKehadiran::where('siswa_id', $siswa_id)->where('semester', $semester_int)->first();
        $catatan = RaporCatatanWali::where('siswa_id', $siswa_id)->where('semester', $semester_int)->first();
        $pkl = RaporPkl::with('dudi')->where('siswa_id', $siswa_id)->where('semester', $semester_int)->get();

        return Inertia::render('Siswa/Rapor/Index', [
            'semua_tahun' => $semua_tahun,
            'selected_ta_id' => $selected_ta_id,
            'jadwal_pelajaran' => $jadwal_pelajaran,
            'rapor_akhir' => $rapor_akhir,
            'formatif' => $formatif,
            'sumatif' => $sumatif,
            'kehadiran' => $kehadiran,
            'catatan' => $catatan,
            'pkl' => $pkl,
            'siswa_id' => $siswa_id
        ]);
    }
}
