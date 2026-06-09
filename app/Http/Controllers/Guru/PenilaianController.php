<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Mapel;
use App\Models\TujuanPembelajaran;
use App\Models\NilaiFormatif;
use App\Models\NilaiSumatif;
use App\Models\RaporAkhir;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\NilaiAkhir;
use App\Models\NilaiSikapK13;
use App\Models\RombonganBelajar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenilaianController extends Controller
{
    /**
     * Halaman Utama Penilaian (Pilih Mapel & Kelas)
     */
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? 1; // Sesuaikan dengan relasi user ke guru
        
        // Ambil data mapel yang diajar oleh guru ini (Simulasi, sesuaikan dengan tabel PembagianTugas di app)
        $mapel = Mapel::all(); 
        
        return Inertia::render('Guru/Penilaian/Index', [
            'mapel' => $mapel
        ]);
    }

    /**
     * Halaman Tujuan Pembelajaran (TP)
     */
    public function tp(Request $request)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        
        $tp = TujuanPembelajaran::with('mapel')->where('guru_id', $guru_id)->get();
        $mapel = Mapel::all();

        return Inertia::render('Guru/Penilaian/TujuanPembelajaran', [
            'tp' => $tp,
            'mapel' => $mapel
        ]);
    }

    /**
     * Simpan Tujuan Pembelajaran
     */
    public function storeTp(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required',
            'kode_tp' => 'required',
            'deskripsi' => 'required',
            'semester' => 'required',
            'tingkat' => 'required',
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;

        TujuanPembelajaran::create([
            'mapel_id' => $request->mapel_id,
            'guru_id' => $guru_id,
            'kode_tp' => $request->kode_tp,
            'deskripsi' => $request->deskripsi,
            'semester' => $request->semester,
            'tingkat' => $request->tingkat,
        ]);

        return redirect()->back()->with('success', 'Tujuan Pembelajaran berhasil ditambahkan.');
    }

    public function destroyTp($id)
    {
        TujuanPembelajaran::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }

    /**
     * Halaman Input Nilai Formatif
     */
    public function formatif(Request $request)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $tp_list = TujuanPembelajaran::where('guru_id', $guru_id)->get();
        $kelas_list = Kelas::all();

        // Jika user memilih kelas dan TP tertentu untuk dinilai
        $siswa = [];
        $nilai_formatif = [];
        if ($request->has('kelas_id') && $request->has('tp_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)->get();
            $nilai_formatif = NilaiFormatif::where('tp_id', $request->tp_id)->get()->keyBy('siswa_id');
        }

        return Inertia::render('Guru/Penilaian/Formatif', [
            'tp_list' => $tp_list,
            'kelas_list' => $kelas_list,
            'siswa' => $siswa,
            'nilai_formatif' => $nilai_formatif,
            'filters' => $request->only(['kelas_id', 'tp_id'])
        ]);
    }

    /**
     * Simpan Nilai Formatif Massal
     */
    public function storeFormatif(Request $request)
    {
        $request->validate([
            'tp_id' => 'required',
            'nilai' => 'required|array', // [siswa_id => nilai]
        ]);

        foreach ($request->nilai as $siswa_id => $nilai) {
            if ($nilai !== null) {
                NilaiFormatif::updateOrCreate(
                    ['tp_id' => $request->tp_id, 'siswa_id' => $siswa_id],
                    ['nilai' => $nilai]
                );
            }
        }

        return redirect()->back()->with('success', 'Nilai Formatif berhasil disimpan.');
    }

    /**
     * Halaman Input Nilai Sumatif (Akhir Semester)
     */
    public function sumatif(Request $request)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $mapel_list = Mapel::all(); // Seharusnya berdasarkan pembagian tugas
        $kelas_list = Kelas::all();
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        $siswa = [];
        $nilai_sumatif = [];
        if ($request->has('kelas_id') && $request->has('mapel_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)->get();
            $nilai_sumatif = NilaiSumatif::where('mapel_id', $request->mapel_id)
                ->where('guru_id', $guru_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                ->where('semester', 1) // Asumsi semester 1 sementara
                ->get()->keyBy('siswa_id');
        }

        return Inertia::render('Guru/Penilaian/Sumatif', [
            'mapel_list' => $mapel_list,
            'kelas_list' => $kelas_list,
            'siswa' => $siswa,
            'nilai_sumatif' => $nilai_sumatif,
            'filters' => $request->only(['kelas_id', 'mapel_id'])
        ]);
    }

    /**
     * Simpan Nilai Sumatif Massal
     */
    public function storeSumatif(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required',
            'jenis' => 'required', // STS atau SAS
            'nilai' => 'required|array',
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        foreach ($request->nilai as $siswa_id => $nilai) {
            if ($nilai !== null) {
                NilaiSumatif::updateOrCreate(
                    [
                        'mapel_id' => $request->mapel_id,
                        'guru_id' => $guru_id,
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => 1,
                        'jenis' => $request->jenis
                    ],
                    ['nilai' => $nilai]
                );
            }
        }

        return redirect()->back()->with('success', 'Nilai Sumatif berhasil disimpan.');
    }

    public function sikapK13(Request $request)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $rombels = RombonganBelajar::with(['kelas', 'mapel'])->where('guru_id', $guru_id)->get();
        $selected_rombel_id = request('rombel_id');
        
        $siswa = [];
        $nilaiSikap = [];

        if ($selected_rombel_id) {
            $rombel = RombonganBelajar::find($selected_rombel_id);
            if ($rombel) {
                // Dummy fetch siswa by kelas
                $siswa = Siswa::where('kelas_id', $rombel->kelas_id)->get();
                $nilai_data = NilaiSikapK13::whereIn('siswa_id', $siswa->pluck('id'))
                    ->where('guru_id', $guru_id)
                    ->get();
                
                foreach ($nilai_data as $n) {
                    $nilaiSikap[$n->siswa_id] = [
                        'nilai_spiritual' => $n->nilai_spiritual,
                        'deskripsi_spiritual' => $n->deskripsi_spiritual,
                        'nilai_sosial' => $n->nilai_sosial,
                        'deskripsi_sosial' => $n->deskripsi_sosial,
                    ];
                }
            }
        }

        return Inertia::render('Guru/Penilaian/SikapK13', [
            'rombels' => $rombels,
            'selected_rombel_id' => $selected_rombel_id,
            'siswa' => $siswa,
            'nilai_sikap' => $nilaiSikap
        ]);
    }

    public function storeSikapK13(Request $request)
    {
        $request->validate(['data' => 'required|array']);
        $guru_id = Auth::user()->guru->id ?? 1;

        foreach ($request->data as $siswa_id => $nilai) {
            NilaiSikapK13::updateOrCreate(
                ['siswa_id' => $siswa_id, 'guru_id' => $guru_id, 'semester' => 1],
                [
                    'nilai_spiritual' => $nilai['nilai_spiritual'] ?? null,
                    'deskripsi_spiritual' => $nilai['deskripsi_spiritual'] ?? null,
                    'nilai_sosial' => $nilai['nilai_sosial'] ?? null,
                    'deskripsi_sosial' => $nilai['deskripsi_sosial'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Nilai Sikap K13 berhasil disimpan.');
    }

    /**
     * Proses/Generate Nilai Akhir Rapor & Deskripsi Otomatis
     */
    public function generateNilaiAkhir(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required',
            'kelas_id' => 'required'
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $siswa_list = Siswa::where('kelas_id', $request->kelas_id)->get();

        foreach ($siswa_list as $siswa) {
            // Logika Sederhana Kurikulum Merdeka:
            // Rata-rata Nilai Formatif
            $tps = TujuanPembelajaran::where('mapel_id', $request->mapel_id)->where('guru_id', $guru_id)->get();
            $tp_ids = $tps->pluck('id')->toArray();
            
            $formatifs = NilaiFormatif::where('siswa_id', $siswa->id)->whereIn('tp_id', $tp_ids)->get();
            $rata_formatif = $formatifs->avg('nilai') ?? 0;

            // Nilai Sumatif SAS
            $sumatif = NilaiSumatif::where('siswa_id', $siswa->id)
                ->where('mapel_id', $request->mapel_id)
                ->where('jenis', 'SAS')
                ->first();
            $nilai_sas = $sumatif ? $sumatif->nilai : 0;

            // Nilai Akhir (Misal 60% Formatif + 40% Sumatif)
            $nilai_akhir = ($rata_formatif * 0.6) + ($nilai_sas * 0.4);

            // Mencari TP tertinggi dan terendah untuk deskripsi
            $tertinggi = $formatifs->sortByDesc('nilai')->first();
            $terendah = $formatifs->sortBy('nilai')->first();

            $deskripsi_tertinggi = "";
            if ($tertinggi && $tertinggi->nilai >= 85) {
                $tp_tinggi = $tps->where('id', $tertinggi->tp_id)->first();
                $deskripsi_tertinggi = "Sangat baik dalam " . ($tp_tinggi ? $tp_tinggi->deskripsi : '');
            }

            $deskripsi_terendah = "";
            if ($terendah && $terendah->nilai < 70) {
                $tp_rendah = $tps->where('id', $terendah->tp_id)->first();
                $deskripsi_terendah = "Perlu pendampingan dalam " . ($tp_rendah ? $tp_rendah->deskripsi : '');
            }

            RaporAkhir::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'mapel_id' => $request->mapel_id,
                    'guru_id' => $guru_id,
                    'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                    'semester' => 1
                ],
                [
                    'nilai_akhir' => round($nilai_akhir),
                    'deskripsi_tertinggi' => $deskripsi_tertinggi,
                    'deskripsi_terendah' => $deskripsi_terendah,
                ]
            );
        }

        return redirect()->back()->with('success', 'Berhasil melakukan generate Nilai Akhir dan Deskripsi otomatis untuk seluruh siswa di kelas.');
    }
}
