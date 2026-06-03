<?php

namespace App\Http\Controllers\Admin\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PembagianTugas;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;

class PembagianTugasController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();
        if (!$tahunAktif) {
            return redirect()->route('admin.dashboard')->with('error', 'Tahun ajaran aktif belum disetting.');
        }

        $tab = $request->input('tab', 'kelas'); // 'kelas' or 'mapel'

        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas', 'ASC')->get();
        $mapel = Mapel::orderBy('kelompok', 'ASC')->orderBy('nama_mapel', 'ASC')->get();
        $guru = Guru::orderBy('nama_lengkap', 'ASC')->get();
        
        // Load existing mapping
        $mapping = PembagianTugas::with(['guru'])
            ->where('id_tahun_ajaran', $tahunAktif->id)
            ->get();

        return Inertia::render('Admin/Kurikulum/PembagianTugas/Index', [
            'tahun_aktif' => $tahunAktif,
            'kelas' => $kelas,
            'mapel' => $mapel,
            'guru' => $guru,
            'mapping' => $mapping,
            'active_tab' => $tab
        ]);
    }

    public function updateMapping(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|integer',
            'id_mapel' => 'required|integer',
            'id_guru' => 'nullable|integer',
            'beban_jam' => 'nullable|integer',
        ]);

        $tahunAktif = TahunAjaran::where('status', 'Aktif')->first();
        if (!$tahunAktif) {
            return response()->json(['error' => 'Tahun ajaran aktif tidak ditemukan'], 400);
        }

        if (empty($request->id_guru) && empty($request->beban_jam)) {
            // Remove mapping if guru is empty/unselected and beban jam is 0/empty
            PembagianTugas::where('id_tahun_ajaran', $tahunAktif->id)
                ->where('id_kelas', $request->id_kelas)
                ->where('id_mapel', $request->id_mapel)
                ->delete();
            
            return response()->json(['success' => true, 'message' => 'Mapping dihapus']);
        }

        // Update or Create mapping
        $mapping = PembagianTugas::updateOrCreate(
            [
                'id_tahun_ajaran' => $tahunAktif->id,
                'id_kelas' => $request->id_kelas,
                'id_mapel' => $request->id_mapel,
            ],
            [
                'id_guru' => $request->id_guru,
                'beban_jam' => $request->beban_jam ?? 0
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => 'Mapping berhasil disimpan',
            'data' => $mapping
        ]);
    }
}
