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
        
        $mapel_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_mapel')->unique();
        $mapel = Mapel::whereIn('id', $mapel_ids)->get(); 
        
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
        
        // Dapatkan semua jadwal yang diajar oleh guru ini
        $jadwals = \App\Models\JadwalPelajaran::with(['mapel', 'kelas'])
            ->where('id_guru', $guru_id)
            ->get();

        $mapel_groups = [];

        foreach ($jadwals as $jadwal) {
            if (!$jadwal->mapel || !$jadwal->kelas) continue;
            
            $mapel_id = $jadwal->id_mapel;
            
            $tingkatNum = $jadwal->kelas->tingkat;
            $tingkatStr = 'Fase E (Kelas 10)';
            if ($tingkatNum == 10) $tingkatStr = 'Fase E (Kelas 10)';
            elseif ($tingkatNum == 11) $tingkatStr = 'Fase F (Kelas 11)';
            elseif ($tingkatNum == 12) $tingkatStr = 'Fase F (Kelas 12)';
            
            if (!isset($mapel_groups[$mapel_id])) {
                $mapel_groups[$mapel_id] = [];
            }
            if (!in_array($tingkatStr, $mapel_groups[$mapel_id])) {
                $mapel_groups[$mapel_id][] = $tingkatStr;
            }
        }

        $mapel_list = [];
        foreach ($mapel_groups as $mapel_id => $phases) {
            $mapelModel = \App\Models\Mapel::find($mapel_id);
            if (!$mapelModel) continue;
            
            if (count($phases) > 1) {
                foreach ($phases as $phase) {
                    $mapel_list[] = [
                        'id' => $mapel_id . '|' . $phase,
                        'nama_mapel' => $mapelModel->nama_mapel . ' (' . $phase . ')'
                    ];
                }
            } else {
                $mapel_list[] = [
                    'id' => $mapel_id . '|' . $phases[0],
                    'nama_mapel' => $mapelModel->nama_mapel
                ];
            }
        }

        return Inertia::render('Guru/Penilaian/TujuanPembelajaran', [
            'tp' => $tp,
            'mapel' => $mapel_list
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
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester = $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1;

        // Parse mapel_id dan tingkatStr dari input
        $parts = explode('|', $request->mapel_id);
        $real_mapel_id = $parts[0];
        $tingkatStr = count($parts) > 1 ? $parts[1] : 'Fase E (Kelas 10)'; // Default fallback

        TujuanPembelajaran::create([
            'mapel_id' => $real_mapel_id,
            'guru_id' => $guru_id,
            'kode_tp' => $request->kode_tp,
            'deskripsi' => $request->deskripsi,
            'semester' => $semester,
            'tingkat' => $tingkatStr,
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
        
        $jadwals = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->get();
        $kelas_ids = $jadwals->pluck('id_kelas')->unique();
        $mapel_ids = $jadwals->pluck('id_mapel')->unique();
        
        $kelas_list = Kelas::whereIn('id', $kelas_ids)->get();
        $mapel_list = Mapel::whereIn('id', $mapel_ids)->get();
        
        // Filter TP list based on selected mapel if available
        $tp_query = TujuanPembelajaran::where('guru_id', $guru_id);
        if ($request->has('mapel_id') && $request->mapel_id != '') {
            $tp_query->where('mapel_id', $request->mapel_id);
        }
        
        // Filter by class phase (tingkat)
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $k = Kelas::find($request->kelas_id);
            if ($k) {
                $tingkatNum = $k->tingkat;
                $tingkatStr = 'Fase E (Kelas 10)';
                if ($tingkatNum == 10) $tingkatStr = 'Fase E (Kelas 10)';
                elseif ($tingkatNum == 11) $tingkatStr = 'Fase F (Kelas 11)';
                elseif ($tingkatNum == 12) $tingkatStr = 'Fase F (Kelas 12)';
                $tp_query->where('tingkat', $tingkatStr);
            }
        }
        
        $tp_list = $tp_query->get();

        // Jika user memilih kelas, mapel dan TP tertentu untuk dinilai
        $siswa = [];
        $nilai_formatif = [];
        
        if ($request->has('kelas_id') && $request->has('mapel_id') && $request->has('tp_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                          ->orderBy('nama_lengkap', 'asc')
                          ->get();
                          
            if ($request->tp_id == 'all') {
                // Fetch all formatifs for this mapel & kelas
                $tp_ids = $tp_list->pluck('id');
                $formatifs = NilaiFormatif::whereIn('tp_id', $tp_ids)
                                          ->whereIn('siswa_id', $siswa->pluck('id'))
                                          ->get();
                // Group by siswa_id then tp_id
                foreach($formatifs as $nf) {
                    $nilai_formatif[$nf->siswa_id][$nf->tp_id] = $nf;
                }
            } else {
                $formatifs = NilaiFormatif::where('tp_id', $request->tp_id)
                                          ->whereIn('siswa_id', $siswa->pluck('id'))
                                          ->get()->keyBy('siswa_id');
                // Format matching old behavior but under tp_id for consistency, or we can leave it flat
                // Flat is expected by vue currently, let's keep it flat if not 'all', but 'all' isn't supported in UI table yet. 
                // Wait, if 'all', UI input table will just be hidden or we tell them to use Excel?
                // The user only requested Excel import for 'all', but if they select 'all' in UI, the table might break.
                // Let's pass flat for single, grouped for 'all'.
                $nilai_formatif = $formatifs;
            }
        }

        return Inertia::render('Guru/Penilaian/Formatif', [
            'mapel_list' => $mapel_list,
            'kelas_list' => $kelas_list,
            'tp_list' => $tp_list,
            'siswa' => $siswa,
            'nilai_formatif' => $nilai_formatif,
            'filters' => $request->only(['kelas_id', 'mapel_id', 'tp_id'])
        ]);
    }

    /**
     * Simpan Nilai Formatif Massal
     */
    public function storeFormatif(Request $request)
    {
        $request->validate([
            'tp_id' => 'required',
            'nilai' => 'required|array', // [siswa_id => nilai] OR [siswa_id => [tp_id => nilai]]
        ]);

        $isAll = $request->tp_id === 'all';

        foreach ($request->nilai as $siswa_id => $nilai_data) {
            if ($isAll) {
                // $nilai_data is an array of [tp_id => nilai]
                if (is_array($nilai_data)) {
                    foreach($nilai_data as $tp_id => $n) {
                        if ($n !== null && $n !== '') {
                            NilaiFormatif::updateOrCreate(
                                ['tp_id' => $tp_id, 'siswa_id' => $siswa_id],
                                ['nilai' => $n]
                            );
                        }
                    }
                }
            } else {
                // $nilai_data is a scalar
                if ($nilai_data !== null && $nilai_data !== '') {
                    NilaiFormatif::updateOrCreate(
                        ['tp_id' => $request->tp_id, 'siswa_id' => $siswa_id],
                        ['nilai' => $nilai_data]
                    );
                }
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
        
        $mapel_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_mapel')->unique();
        $mapel_list = Mapel::whereIn('id', $mapel_ids)->get();

        $kelas_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_kelas')->unique();
        $kelas_list = Kelas::whereIn('id', $kelas_ids)->get();
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $siswa = [];
        $nilai_sumatif = [];
        if ($request->has('kelas_id') && $request->has('mapel_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                          ->orderBy('nama_lengkap', 'asc')
                          ->get();
            $sumatifs = NilaiSumatif::where('mapel_id', $request->mapel_id)
                ->where('guru_id', $guru_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                ->where('semester', $semester_int)
                ->get();
            foreach($sumatifs as $ns) {
                $nilai_sumatif[$ns->siswa_id][$ns->jenis] = $ns;
            }
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
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        foreach ($request->nilai as $siswa_id => $nilai) {
            if ($nilai !== null) {
                NilaiSumatif::updateOrCreate(
                    [
                        'mapel_id' => $request->mapel_id,
                        'guru_id' => $guru_id,
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => $semester_int,
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
        $rombels = \App\Models\JadwalPelajaran::with(['kelas', 'mapel'])
            ->where('id_guru', $guru_id)
            ->get()
            ->unique(function ($item) {
                return $item['id_kelas'].'-'.$item['id_mapel'];
            })->values();

        $selected_rombel_id = request('rombel_id');
        
        $siswa = [];
        $nilaiSikap = [];

        if ($selected_rombel_id) {
            $rombel = \App\Models\JadwalPelajaran::find($selected_rombel_id);
            if ($rombel) {
                $siswa = Siswa::where('kelas_id', $rombel->id_kelas)->orderBy('nama_lengkap', 'asc')->get();
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
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        foreach ($request->data as $siswa_id => $nilai) {
            NilaiSikapK13::updateOrCreate(
                ['siswa_id' => $siswa_id, 'guru_id' => $guru_id, 'semester' => $semester_int],
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

    public function halamanGenerateNilaiAkhir(Request $request)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;
        
        $mapel_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_mapel')->unique();
        $mapel_list = Mapel::whereIn('id', $mapel_ids)->get();

        $kelas_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_kelas')->unique();
        $kelas_list = Kelas::whereIn('id', $kelas_ids)->get();

        $siswa = [];
        $rapor_akhir = [];
        $tps = [];
        $detail_nilai = [];
        if ($request->has('kelas_id') && $request->has('mapel_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama_lengkap', 'asc')->get();
            
            $k = Kelas::find($request->kelas_id);
            $tingkatStr = 'Fase E (Kelas 10)';
            if ($k) {
                $tingkatNum = $k->tingkat;
                if ($tingkatNum == 10) $tingkatStr = 'Fase E (Kelas 10)';
                elseif ($tingkatNum == 11) $tingkatStr = 'Fase F (Kelas 11)';
                elseif ($tingkatNum == 12) $tingkatStr = 'Fase F (Kelas 12)';
            }
            
            $tps = TujuanPembelajaran::where('mapel_id', $request->mapel_id)
                        ->where('guru_id', $guru_id)
                        ->where('tingkat', $tingkatStr)
                        ->get();
            $tp_ids = $tps->pluck('id')->toArray();
            
            $formatifs = NilaiFormatif::whereIn('siswa_id', $siswa->pluck('id'))->whereIn('tp_id', $tp_ids)->get();
            $sumatifs = NilaiSumatif::whereIn('siswa_id', $siswa->pluck('id'))
                ->where('mapel_id', $request->mapel_id)
                ->where('guru_id', $guru_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                ->where('semester', $semester_int)
                ->get();

            foreach($siswa as $s) {
                $f = $formatifs->where('siswa_id', $s->id)->keyBy('tp_id');
                $sas = $sumatifs->where('siswa_id', $s->id)->where('jenis', 'SAS')->first();
                $sts = $sumatifs->where('siswa_id', $s->id)->where('jenis', 'STS')->first();
                $detail_nilai[$s->id] = [
                    'formatif' => $f,
                    'sas' => $sas ? $sas->nilai : '-',
                    'sts' => $sts ? $sts->nilai : '-'
                ];
            }

            $rapor_akhir = RaporAkhir::where('mapel_id', $request->mapel_id)
                ->where('guru_id', $guru_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                ->where('semester', $semester_int)
                ->whereIn('siswa_id', $siswa->pluck('id'))
                ->get()
                ->keyBy('siswa_id');
        }

        return Inertia::render('Guru/Penilaian/GenerateNilai', [
            'mapel_list' => $mapel_list,
            'kelas_list' => $kelas_list,
            'siswa' => $siswa,
            'rapor_akhir' => $rapor_akhir,
            'tps' => $tps,
            'detail_nilai' => $detail_nilai,
            'filters' => $request->only(['kelas_id', 'mapel_id'])
        ]);
    }

    /**
     * Proses/Generate Nilai Akhir Rapor & Deskripsi Otomatis
     */
    public function generateNilaiAkhir(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required',
            'kelas_id' => 'required',
            'kkm' => 'required|numeric|min:0|max:100',
            'batas_sangat_baik' => 'required|numeric|min:0|max:100'
        ]);

        $kkm = $request->kkm;
        $batas_sangat_baik = $request->batas_sangat_baik;

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;
        $siswa_list = Siswa::where('kelas_id', $request->kelas_id)->get();

        $k = Kelas::find($request->kelas_id);
        $tingkatStr = 'Fase E (Kelas 10)';
        if ($k) {
            $tingkatNum = $k->tingkat;
            if ($tingkatNum == 10) $tingkatStr = 'Fase E (Kelas 10)';
            elseif ($tingkatNum == 11) $tingkatStr = 'Fase F (Kelas 11)';
            elseif ($tingkatNum == 12) $tingkatStr = 'Fase F (Kelas 12)';
        }

        foreach ($siswa_list as $siswa) {
            // Logika Sederhana Kurikulum Merdeka:
            // Rata-rata Nilai Formatif
            $tps = TujuanPembelajaran::where('mapel_id', $request->mapel_id)
                        ->where('guru_id', $guru_id)
                        ->where('tingkat', $tingkatStr)
                        ->get();
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

            $sangat_baik_tps = [];
            $baik_tps = [];
            $kurang_tps = [];

            foreach ($formatifs as $f) {
                $tp = $tps->where('id', $f->tp_id)->first();
                if ($tp) {
                    if ($f->nilai >= $batas_sangat_baik) {
                        $sangat_baik_tps[] = $tp->deskripsi;
                    } elseif ($f->nilai >= $kkm) {
                        $baik_tps[] = $tp->deskripsi;
                    } else {
                        $kurang_tps[] = $tp->deskripsi;
                    }
                }
            }

            $deskripsi_parts = [];
            if (count($sangat_baik_tps) > 0) {
                $deskripsi_parts[] = "Menunjukkan penguasaan yang sangat baik dalam " . implode(", ", $sangat_baik_tps);
            }
            if (count($baik_tps) > 0) {
                $deskripsi_parts[] = "Menunjukkan penguasaan yang baik dalam " . implode(", ", $baik_tps);
            }
            if (count($kurang_tps) > 0) {
                $deskripsi_parts[] = "Perlu pendampingan dalam " . implode(", ", $kurang_tps);
            }

            $deskripsi_tertinggi = implode(". ", $deskripsi_parts);
            if (!empty($deskripsi_tertinggi)) {
                $deskripsi_tertinggi .= ".";
            }
            $deskripsi_terendah = "";

            RaporAkhir::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'mapel_id' => $request->mapel_id,
                    'guru_id' => $guru_id,
                    'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                    'semester' => $semester_int
                ],
                [
                    'nilai_akhir' => round($nilai_akhir),
                    'deskripsi_tertinggi' => $deskripsi_tertinggi,
                    'deskripsi_terendah' => $deskripsi_terendah,
                ]
            );
        }

        return redirect()->route('guru.penilaian.halaman_generate_nilai_akhir', [
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'kkm' => $kkm,
            'batas_sangat_baik' => $batas_sangat_baik
        ])->with('success', 'Berhasil melakukan generate Nilai Akhir dan Deskripsi otomatis untuk seluruh siswa di kelas.');
    }

    // ==============================================================================
    // EXCEL IMPORT / EXPORT (PhpSpreadsheet)
    // ==============================================================================

    public function templateFormatif(Request $request)
    {
        $request->validate(['tp_id' => 'required', 'kelas_id' => 'required']);
        $kelas = Kelas::find($request->kelas_id);
        $siswa = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama_lengkap', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'TEMPLATE NILAI FORMATIF');
        $sheet->setCellValue('A4', 'Kelas: ' . ($kelas->nama_kelas ?? ''));
        $sheet->setCellValue('A6', 'ID SISWA (JANGAN DIUBAH)');
        $sheet->setCellValue('B6', 'NISN');
        $sheet->setCellValue('C6', 'NAMA SISWA');

        $isAll = $request->tp_id === 'all';
        $tps = [];
        
        if ($isAll) {
            $request->validate(['mapel_id' => 'required']);
            $mapel = Mapel::find($request->mapel_id);
            $sheet->setCellValue('A2', 'Mata Pelajaran: ' . ($mapel->nama_mapel ?? ''));
            $sheet->setCellValue('A3', 'Tujuan Pembelajaran: SEMUA TP');
            
            $tingkatStr = 'Fase E (Kelas 10)';
            if ($kelas) {
                $tingkatNum = $kelas->tingkat;
                if ($tingkatNum == 10) $tingkatStr = 'Fase E (Kelas 10)';
                elseif ($tingkatNum == 11) $tingkatStr = 'Fase F (Kelas 11)';
                elseif ($tingkatNum == 12) $tingkatStr = 'Fase F (Kelas 12)';
            }
            
            $tps = TujuanPembelajaran::where('mapel_id', $request->mapel_id)
                        ->where('guru_id', Auth::user()->guru->id ?? 1)
                        ->where('tingkat', $tingkatStr)
                        ->get();
            
            $colIndex = 4; // D
            foreach($tps as $tp) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($colLetter.'6', $tp->kode_tp);
                $colIndex++;
            }
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        } else {
            $tp = TujuanPembelajaran::find($request->tp_id);
            $tps = [$tp];
            $sheet->setCellValue('A2', 'Mata Pelajaran: ' . ($tp->mapel->nama_mapel ?? ''));
            $sheet->setCellValue('A3', 'Tujuan Pembelajaran: ' . ($tp->kode_tp ?? ''));
            $sheet->setCellValue('D6', 'NILAI FORMATIF (0-100)');
            $lastColLetter = 'D';
        }

        $row = 7;
        foreach($siswa as $s) {
            $sheet->setCellValue('A'.$row, $s->id);
            $sheet->setCellValue('B'.$row, $s->nisn);
            $sheet->setCellValue('C'.$row, $s->nama_lengkap);
            $row++;
        }

        // Auto size columns
        $highestColumn = $sheet->getHighestColumn();
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add Styles
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $styleData = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        
        $sheet->getStyle('A6:'.$lastColLetter.'6')->applyFromArray($styleHeader);
        if ($row > 7) {
            $sheet->getStyle('A7:'.$lastColLetter.($row - 1))->applyFromArray($styleData);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Formatif_'.$kelas->nama_kelas.'_'.($isAll ? 'Semua_TP' : $tps[0]->kode_tp).'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function importFormatif(Request $request)
    {
        $request->validate([
            'tp_id' => 'required',
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $headers = $rows[5]; // Row 6 is index 5
        $data = array_slice($rows, 6); // Skip header

        $isAll = $request->tp_id === 'all';
        $tps = [];
        
        if ($isAll) {
            $request->validate(['mapel_id' => 'required']);
            // Map headers to TP IDs based on kode_tp
            $tpList = TujuanPembelajaran::where('mapel_id', $request->mapel_id)->where('guru_id', Auth::user()->guru->id ?? 1)->get();
            $tpMap = []; // column index => tp_id
            foreach ($headers as $index => $headerName) {
                if ($index >= 3 && $headerName) { // Start from col D
                    $matchedTp = $tpList->firstWhere('kode_tp', $headerName);
                    if ($matchedTp) {
                        $tpMap[$index] = $matchedTp->id;
                    }
                }
            }
            
            foreach($data as $row) {
                $siswa_id = $row[0];
                if($siswa_id) {
                    foreach($tpMap as $colIndex => $tpId) {
                        $nilai = $row[$colIndex];
                        if (is_numeric($nilai)) {
                            NilaiFormatif::updateOrCreate(
                                ['tp_id' => $tpId, 'siswa_id' => $siswa_id],
                                ['nilai' => $nilai]
                            );
                        }
                    }
                }
            }
        } else {
            foreach($data as $row) {
                $siswa_id = $row[0];
                $nilai = $row[3]; // Col D

                if($siswa_id && is_numeric($nilai)) {
                    NilaiFormatif::updateOrCreate(
                        ['tp_id' => $request->tp_id, 'siswa_id' => $siswa_id],
                        ['nilai' => $nilai]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Import Nilai Formatif berhasil.');
    }

    public function templateSumatif(Request $request)
    {
        $request->validate(['mapel_id' => 'required', 'kelas_id' => 'required', 'jenis' => 'required']);
        $mapel = Mapel::find($request->mapel_id);
        $kelas = Kelas::find($request->kelas_id);
        $siswa = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama_lengkap', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'TEMPLATE NILAI SUMATIF (' . $request->jenis . ')');
        $sheet->setCellValue('A2', 'Mata Pelajaran: ' . ($mapel->nama_mapel ?? ''));
        $sheet->setCellValue('A3', 'Kelas: ' . ($kelas->nama_kelas ?? ''));
        
        $sheet->setCellValue('A5', 'ID SISWA (JANGAN DIUBAH)');
        $sheet->setCellValue('B5', 'NISN');
        $sheet->setCellValue('C5', 'NAMA SISWA');

        $isBoth = $request->jenis === 'KEDUANYA';
        
        if ($isBoth) {
            $sheet->setCellValue('D5', 'NILAI SAS (0-100)');
            $sheet->setCellValue('E5', 'NILAI STS (0-100)');
            $lastColLetter = 'E';
        } else {
            $sheet->setCellValue('D5', 'NILAI ' . $request->jenis . ' (0-100)');
            $lastColLetter = 'D';
        }

        $row = 6;
        foreach($siswa as $s) {
            $sheet->setCellValue('A'.$row, $s->id);
            $sheet->setCellValue('B'.$row, $s->nisn);
            $sheet->setCellValue('C'.$row, $s->nama_lengkap);
            $row++;
        }

        foreach (range('A', $lastColLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add Styles
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF9333EA']], // Purple 600
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $styleData = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A5:'.$lastColLetter.'5')->applyFromArray($styleHeader);
        if ($row > 6) {
            $sheet->getStyle('A6:'.$lastColLetter . ($row - 1))->applyFromArray($styleData);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Sumatif_'.str_replace(' ', '_', $request->jenis).'_'.$kelas->nama_kelas.'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function importSumatif(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required',
            'jenis' => 'required',
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $data = array_slice($rows, 5);
        $isBoth = $request->jenis === 'KEDUANYA';

        foreach($data as $row) {
            $siswa_id = $row[0];
            
            if ($isBoth) {
                $nilai_sas = $row[3]; // Col D
                $nilai_sts = $row[4]; // Col E
                
                if($siswa_id) {
                    if (is_numeric($nilai_sas)) {
                        NilaiSumatif::updateOrCreate(
                            [
                                'mapel_id' => $request->mapel_id,
                                'guru_id' => $guru_id,
                                'siswa_id' => $siswa_id,
                                'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                                'semester' => $semester_int,
                                'jenis' => 'SAS'
                            ],
                            ['nilai' => $nilai_sas]
                        );
                    }
                    if (is_numeric($nilai_sts)) {
                        NilaiSumatif::updateOrCreate(
                            [
                                'mapel_id' => $request->mapel_id,
                                'guru_id' => $guru_id,
                                'siswa_id' => $siswa_id,
                                'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                                'semester' => $semester_int,
                                'jenis' => 'STS'
                            ],
                            ['nilai' => $nilai_sts]
                        );
                    }
                }
            } else {
                $nilai = $row[3]; // Col D
                if($siswa_id && is_numeric($nilai)) {
                    NilaiSumatif::updateOrCreate(
                        [
                            'mapel_id' => $request->mapel_id,
                            'guru_id' => $guru_id,
                            'siswa_id' => $siswa_id,
                            'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                            'semester' => $semester_int,
                            'jenis' => $request->jenis
                        ],
                        ['nilai' => $nilai]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Import Nilai Sumatif berhasil.');
    }

    public function templateSikapK13(Request $request)
    {
        $request->validate(['kelas_id' => 'required']);
        $kelas = Kelas::find($request->kelas_id);
        $siswa = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama_lengkap', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'TEMPLATE NILAI SIKAP K13');
        $sheet->setCellValue('A2', 'Kelas: ' . ($kelas->nama_kelas ?? ''));
        
        $sheet->setCellValue('A4', 'ID SISWA (JANGAN DIUBAH)');
        $sheet->setCellValue('B4', 'NISN');
        $sheet->setCellValue('C4', 'NAMA SISWA');
        $sheet->setCellValue('D4', 'NILAI SPIRITUAL (1-4)');
        $sheet->setCellValue('E4', 'DESKRIPSI SPIRITUAL');
        $sheet->setCellValue('F4', 'NILAI SOSIAL (1-4)');
        $sheet->setCellValue('G4', 'DESKRIPSI SOSIAL');

        $row = 5;
        foreach($siswa as $s) {
            $sheet->setCellValue('A'.$row, $s->id);
            $sheet->setCellValue('B'.$row, $s->nisn);
            $sheet->setCellValue('C'.$row, $s->nama_lengkap);
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add Styles
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDB2777']], // Pink 600
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $styleData = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:G4')->applyFromArray($styleHeader);
        if ($row > 5) {
            $sheet->getStyle('A5:G' . ($row - 1))->applyFromArray($styleData);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_SikapK13_'.$kelas->nama_kelas.'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function importSikapK13(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $data = array_slice($rows, 4);

        foreach($data as $row) {
            $siswa_id = $row[0];
            
            if($siswa_id) {
                NilaiSikapK13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'guru_id' => $guru_id, 'semester' => $semester_int],
                    [
                        'nilai_spiritual' => $row[3] ?? null,
                        'deskripsi_spiritual' => $row[4] ?? null,
                        'nilai_sosial' => $row[5] ?? null,
                        'deskripsi_sosial' => $row[6] ?? null,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Import Nilai Sikap K13 berhasil.');
    }
}
