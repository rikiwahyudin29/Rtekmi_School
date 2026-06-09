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
        
        $mapel_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_mapel')->unique();
        $mapel = Mapel::whereIn('id', $mapel_ids)->get();

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
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester = $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1;

        $jadwal = \App\Models\JadwalPelajaran::with('kelas')
            ->where('id_guru', $guru_id)
            ->where('id_mapel', $request->mapel_id)
            ->first();
            
        $tingkatStr = 'Fase E (Kelas 10)';
        if ($jadwal && $jadwal->kelas) {
            $tingkatNum = $jadwal->kelas->tingkat;
            if ($tingkatNum == 10) $tingkatStr = 'Fase E (Kelas 10)';
            elseif ($tingkatNum == 11) $tingkatStr = 'Fase F (Kelas 11)';
            elseif ($tingkatNum == 12) $tingkatStr = 'Fase F (Kelas 12)';
        }

        TujuanPembelajaran::create([
            'mapel_id' => $request->mapel_id,
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
        $tp_list = TujuanPembelajaran::where('guru_id', $guru_id)->get();
        
        $kelas_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_kelas')->unique();
        $kelas_list = Kelas::whereIn('id', $kelas_ids)->get();

        // Jika user memilih kelas dan TP tertentu untuk dinilai
        $siswa = [];
        $nilai_formatif = [];
        if ($request->has('kelas_id') && $request->has('tp_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                          ->orderBy('nama_lengkap', 'asc')
                          ->get();
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
        
        $mapel_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_mapel')->unique();
        $mapel_list = Mapel::whereIn('id', $mapel_ids)->get();

        $kelas_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_kelas')->unique();
        $kelas_list = Kelas::whereIn('id', $kelas_ids)->get();
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        $siswa = [];
        $nilai_sumatif = [];
        if ($request->has('kelas_id') && $request->has('mapel_id')) {
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                          ->orderBy('nama_lengkap', 'asc')
                          ->get();
            $nilai_sumatif = NilaiSumatif::where('mapel_id', $request->mapel_id)
                ->where('guru_id', $guru_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                ->where('semester', $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1)
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
                        'semester' => $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1,
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
        $semester = $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1;

        foreach ($request->data as $siswa_id => $nilai) {
            NilaiSikapK13::updateOrCreate(
                ['siswa_id' => $siswa_id, 'guru_id' => $guru_id, 'semester' => $semester],
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
        
        $mapel_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_mapel')->unique();
        $mapel_list = Mapel::whereIn('id', $mapel_ids)->get();

        $kelas_ids = \App\Models\JadwalPelajaran::where('id_guru', $guru_id)->pluck('id_kelas')->unique();
        $kelas_list = Kelas::whereIn('id', $kelas_ids)->get();

        return Inertia::render('Guru/Penilaian/GenerateNilai', [
            'mapel_list' => $mapel_list,
            'kelas_list' => $kelas_list
        ]);
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
                    'semester' => $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1
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

    // ==============================================================================
    // EXCEL IMPORT / EXPORT (PhpSpreadsheet)
    // ==============================================================================

    public function templateFormatif(Request $request)
    {
        $request->validate(['tp_id' => 'required', 'kelas_id' => 'required']);
        $tp = TujuanPembelajaran::find($request->tp_id);
        $kelas = Kelas::find($request->kelas_id);
        $siswa = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama_lengkap', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'TEMPLATE NILAI FORMATIF');
        $sheet->setCellValue('A2', 'Mata Pelajaran: ' . ($tp->mapel->nama_mapel ?? ''));
        $sheet->setCellValue('A3', 'Tujuan Pembelajaran: ' . ($tp->kode_tp ?? ''));
        $sheet->setCellValue('A4', 'Kelas: ' . ($kelas->nama_kelas ?? ''));
        
        $sheet->setCellValue('A6', 'ID SISWA (JANGAN DIUBAH)');
        $sheet->setCellValue('B6', 'NISN');
        $sheet->setCellValue('C6', 'NAMA SISWA');
        $sheet->setCellValue('D6', 'NILAI FORMATIF (0-100)');

        $row = 7;
        foreach($siswa as $s) {
            $sheet->setCellValue('A'.$row, $s->id);
            $sheet->setCellValue('B'.$row, $s->nisn);
            $sheet->setCellValue('C'.$row, $s->nama_lengkap);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Formatif_'.$kelas->nama_kelas.'_'.$tp->kode_tp.'.xlsx';
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

        $data = array_slice($rows, 6); // Skip header

        foreach($data as $row) {
            $siswa_id = $row[0];
            $nilai = $row[3];

            if($siswa_id && is_numeric($nilai)) {
                NilaiFormatif::updateOrCreate(
                    ['tp_id' => $request->tp_id, 'siswa_id' => $siswa_id],
                    ['nilai' => $nilai]
                );
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
        $sheet->setCellValue('D5', 'NILAI SUMATIF (0-100)');

        $row = 6;
        foreach($siswa as $s) {
            $sheet->setCellValue('A'.$row, $s->id);
            $sheet->setCellValue('B'.$row, $s->nisn);
            $sheet->setCellValue('C'.$row, $s->nama_lengkap);
            $row++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Sumatif_'.$request->jenis.'_'.$kelas->nama_kelas.'.xlsx';
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

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $data = array_slice($rows, 5);

        foreach($data as $row) {
            $siswa_id = $row[0];
            $nilai = $row[3];

            if($siswa_id && is_numeric($nilai)) {
                NilaiSumatif::updateOrCreate(
                    [
                        'mapel_id' => $request->mapel_id,
                        'guru_id' => $guru_id,
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1,
                        'jenis' => $request->jenis
                    ],
                    ['nilai' => $nilai]
                );
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
        $semester = $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 1;

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $data = array_slice($rows, 4);

        foreach($data as $row) {
            $siswa_id = $row[0];
            
            if($siswa_id) {
                NilaiSikapK13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'guru_id' => $guru_id, 'semester' => $semester],
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
