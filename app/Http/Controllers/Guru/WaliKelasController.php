<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\RaporKehadiran;
use App\Models\RaporCatatanWali;
use App\Models\RaporPkl;
use App\Models\Dudi;
use App\Models\PklK13;
use App\Models\DeskripsiP3K13;
use App\Models\DeskripsiDplK13;
use App\Models\KenaikanKelas;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WaliKelasController extends Controller
{
    /**
     * Mendapatkan kelas di mana guru ini adalah wali kelas.
     * Untuk simulasi, kita kembalikan semua kelas atau kelas ID 1.
     */
    private function getKelasWali()
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $kelas = Kelas::where('guru_id', $guru_id)->first();
        return $kelas;
    }

    public function index()
    {
        $kelas = $this->getKelasWali();
        
        $status_penilaian = [];
        if ($kelas) {
            $siswa_count = Siswa::where('kelas_id', $kelas->id)->count();
            $jadwal = \App\Models\JadwalPelajaran::with(['mapel', 'guru'])
                ->where('id_kelas', $kelas->id)
                ->get()
                ->unique('id_mapel');

            foreach ($jadwal as $j) {
                if ($j->mapel && $j->guru) {
                    $siswa_ids = Siswa::where('kelas_id', $kelas->id)->pluck('id');
                    $nilai_count = \App\Models\RaporAkhir::where('mapel_id', $j->id_mapel)
                        ->whereIn('siswa_id', $siswa_ids)
                        ->count();

                    $status = 'Belum Tuntas';
                    if ($siswa_count == 0) {
                        $status = 'Belum Ada Siswa';
                    } elseif ($nilai_count >= $siswa_count) {
                        $status = 'Tuntas';
                    } elseif ($nilai_count > 0) {
                        $status = "Proses ($nilai_count/$siswa_count)";
                    }

                    $status_penilaian[] = [
                        'mapel' => $j->mapel->nama_mapel,
                        'guru' => $j->guru->nama_lengkap,
                        'status' => $status,
                        'tuntas' => $status === 'Tuntas'
                    ];
                }
            }
        }
        
        return Inertia::render('Guru/WaliKelas/Index', [
            'kelas' => $kelas,
            'status_penilaian' => $status_penilaian
        ]);
    }

    /**
     * Input Kehadiran Siswa
     */
    public function kehadiran()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $kehadiran = RaporKehadiran::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                        ->where('semester', $semester_int)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Kehadiran', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'kehadiran' => $kehadiran
        ]);
    }

    public function storeKehadiran(Request $request)
    {
        $request->validate([
            'input_data' => 'required|array'
        ]);

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        foreach ($request->input_data as $siswa_id => $absen) {
            RaporKehadiran::updateOrCreate(
                [
                    'siswa_id' => $siswa_id,
                    'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                    'semester' => $semester_int
                ],
                [
                    'sakit' => $absen['sakit'] ?? 0,
                    'izin' => $absen['izin'] ?? 0,
                    'tanpa_keterangan' => $absen['tanpa_keterangan'] ?? 0,
                ]
            );
        }

        return redirect()->back()->with('success', 'Data kehadiran berhasil disimpan.');
    }

    public function templateKehadiran()
    {
        $kelas = $this->getKelasWali();
        if (!$kelas) return redirect()->back()->with('error', 'Anda bukan wali kelas.');
        
        $siswa = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap', 'asc')->get();
        
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $kehadiran = RaporKehadiran::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('tahun_ajaran_id', $tahun_ajaran_aktif->id ?? 1)
                        ->where('semester', $semester_int)
                        ->get()->keyBy('siswa_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'] // Indigo color
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        // Border Body
        $bodyStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];

        // Set Headers
        $headers = ['NO', 'SISWA_ID', 'NISN', 'NAMA SISWA', 'SAKIT', 'IZIN', 'TANPA KETERANGAN'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Hide SISWA_ID column
        $sheet->getColumnDimension('B')->setVisible(false);

        $row = 2;
        foreach ($siswa as $index => $s) {
            $k = $kehadiran->get($s->id);
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $s->id);
            $sheet->setCellValue('C' . $row, " " . $s->nisn);
            $sheet->setCellValue('D' . $row, $s->nama_lengkap);
            $sheet->setCellValue('E' . $row, $k ? $k->sakit : 0);
            $sheet->setCellValue('F' . $row, $k ? $k->izin : 0);
            $sheet->setCellValue('G' . $row, $k ? $k->tanpa_keterangan : 0);
            
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($bodyStyle);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Kehadiran_Kelas_' . str_replace(' ', '_', $kelas->nama_kelas) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function importKehadiran(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        for ($row = 2; $row <= $highestRow; $row++) {
            $siswa_id = $sheet->getCell('B' . $row)->getValue();
            if (!$siswa_id) continue;

            $sakit = (int) $sheet->getCell('E' . $row)->getValue();
            $izin = (int) $sheet->getCell('F' . $row)->getValue();
            $tanpa_ket = (int) $sheet->getCell('G' . $row)->getValue();

            RaporKehadiran::updateOrCreate(
                [
                    'siswa_id' => $siswa_id,
                    'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                    'semester' => $semester_int
                ],
                [
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'tanpa_keterangan' => $tanpa_ket,
                ]
            );
        }

        return redirect()->back()->with('success', 'Data kehadiran berhasil diimport.');
    }

    /**
     * Input Catatan Wali Kelas
     */
    public function catatan()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $catatan = RaporCatatanWali::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', $semester_int)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Catatan', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'catatan' => $catatan
        ]);
    }

    public function storeCatatan(Request $request)
    {
        $request->validate([
            'input_data' => 'required|array'
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        foreach ($request->input_data as $siswa_id => $catatan_text) {
            if (!empty($catatan_text)) {
                RaporCatatanWali::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'guru_id' => $guru_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => $semester_int
                    ],
                    [
                        'catatan' => $catatan_text
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Catatan Wali Kelas berhasil disimpan.');
    }

    /**
     * Input Data Praktik Kerja Lapangan (PKL)
     */
    public function pkl()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        $dudi_list = Dudi::all();
        
        $pkl = RaporPkl::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Pkl', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'pkl' => $pkl,
            'dudi_list' => $dudi_list
        ]);
    }

    public function storePkl(Request $request)
    {
        $request->validate([
            'input_data' => 'required|array'
        ]);

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        foreach ($request->input_data as $siswa_id => $p) {
            if (!empty($p['dudi_id']) && !empty($p['lokasi']) && !empty($p['lama_bulan'])) {
                RaporPkl::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => 1
                    ],
                    [
                        'dudi_id' => $p['dudi_id'],
                        'lokasi' => $p['lokasi'],
                        'lama_bulan' => $p['lama_bulan'],
                        'keterangan' => $p['keterangan'] ?? null,
                        'nilai' => $p['nilai'] ?? null,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Data PKL siswa berhasil disimpan.');
    }

    /**
     * Status Kenaikan / Kelulusan Kelas
     */
    public function kenaikan()
    {
        $tahun_ajaran = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
        if ($tahun_ajaran && $tahun_ajaran->semester === 'Ganjil') {
            return redirect()->back()->with('error', 'Status Kenaikan Kelas hanya dapat dikelola pada Semester Genap.');
        }

        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        $kelas_all = Kelas::all();
        
        $kenaikan = KenaikanKelas::whereIn('siswa_id', $siswa->pluck('id'))
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Kenaikan', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'kenaikan' => $kenaikan,
            'kelas_all' => $kelas_all
        ]);
    }

    public function storeKenaikan(Request $request)
    {
        $tahun_ajaran = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
        if ($tahun_ajaran && $tahun_ajaran->semester === 'Ganjil') {
            return redirect()->back()->with('error', 'Status Kenaikan Kelas hanya dapat dikelola pada Semester Genap.');
        }

        $request->validate([
            'input_data' => 'required|array'
        ]);

        foreach ($request->input_data as $siswa_id => $k) {
            if (!empty($k['status'])) {
                KenaikanKelas::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id
                    ],
                    [
                        'status' => $k['status'],
                        'kelas_tujuan_id' => $k['kelas_tujuan_id'] ?? null
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Status Kenaikan Kelas berhasil disimpan.');
    }

    public function cetakRapor()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Guru/WaliKelas/Cetak', [
            'kelas' => $kelas,
            'siswa' => $siswa
        ]);
    }

    /**
     * K13: PKL K13
     */
    public function pklK13()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        $pkl_k13 = PklK13::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/PklK13', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'pkl_k13' => $pkl_k13
        ]);
    }

    public function storePklK13(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $p) {
            if (!empty($p['mitra_du_di'])) {
                PklK13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'semester' => 1],
                    [
                        'mitra_du_di' => $p['mitra_du_di'],
                        'lokasi' => $p['lokasi'] ?? '-',
                        'lama_bulan' => $p['lama_bulan'] ?? 0,
                        'keterangan' => $p['keterangan'] ?? null,
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Data PKL K13 berhasil disimpan.');
    }

    /**
     * K13: Deskripsi P3 K13
     */
    public function deskripsiP3()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        $deskripsi_p3 = DeskripsiP3K13::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/DeskripsiP3', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'deskripsi_p3' => $deskripsi_p3
        ]);
    }

    public function storeDeskripsiP3(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $d) {
            if (!empty($d['deskripsi'])) {
                DeskripsiP3K13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'semester' => 1],
                    ['deskripsi' => $d['deskripsi']]
                );
            }
        }
        return redirect()->back()->with('success', 'Deskripsi Profil Pelajar Pancasila (K13) berhasil disimpan.');
    }

    /**
     * K13: Deskripsi DPL K13
     */
    public function deskripsiDpl()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        $deskripsi_dpl = DeskripsiDplK13::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/DeskripsiDpl', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'deskripsi_dpl' => $deskripsi_dpl
        ]);
    }

    public function storeDeskripsiDpl(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $d) {
            if (!empty($d['deskripsi'])) {
                DeskripsiDplK13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'semester' => 1],
                    ['deskripsi' => $d['deskripsi']]
                );
            }
        }
        return redirect()->back()->with('success', 'Deskripsi Perkembangan Lulusan (K13) berhasil disimpan.');
    }

    /**
     * Edit Data Siswa oleh Wali Kelas
     */
    public function dataSiswa()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Guru/WaliKelas/DataSiswa', [
            'kelas' => $kelas,
            'siswa' => $siswa
        ]);
    }

    public function updateDataSiswa(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $request->validate([
            'nisn'           => 'required|unique:tbl_siswa,nisn,' . $id,
            'nama_lengkap'   => 'required|string|max:100',
        ]);

        $siswa->update([
            'nisn'             => $request->nisn,
            'nis'              => $request->nis,
            'nama_lengkap'     => $request->nama_lengkap,
            'jenis_kelamin'    => $request->jenis_kelamin,
            'tempat_lahir'     => $request->tempat_lahir,
            'tanggal_lahir'    => $request->tanggal_lahir,
            'agama'            => $request->agama,
            'alamat'           => $request->alamat,
            'no_hp_siswa'      => $request->no_hp_siswa,
            'status_keluarga'  => $request->status_keluarga,
            'anak_ke'          => $request->anak_ke,
            'sekolah_asal'     => $request->sekolah_asal,
            'diterima_kelas'   => $request->diterima_kelas,
            'tanggal_diterima' => $request->tanggal_diterima,
            'nama_ayah'        => $request->nama_ayah,
            'nama_ibu'         => $request->nama_ibu,
            'pekerjaan_ayah'   => $request->pekerjaan_ayah,
            'pekerjaan_ibu'    => $request->pekerjaan_ibu,
            'no_hp_ortu'       => $request->no_hp_ortu,
            'nama_wali'        => $request->nama_wali,
            'pekerjaan_wali'   => $request->pekerjaan_wali,
            'alamat_wali'      => $request->alamat_wali,
            'no_hp_wali'       => $request->no_hp_wali,
        ]);

        return redirect()->back()->with('success', 'Data biodata siswa berhasil diperbarui.');
    }

    /**
     * Edit Nilai Siswa Binaan oleh Wali Kelas
     */
    public function nilaiSiswa()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $rapor_akhir = \App\Models\RaporAkhir::with('mapel')
                        ->whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', $semester_int)
                        ->get()
                        ->groupBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/NilaiSiswa', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'rapor_akhir' => $rapor_akhir
        ]);
    }

    public function updateNilaiSiswa(Request $request, $siswa_id)
    {
        $request->validate([
            'nilai' => 'required|array'
        ]);

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        foreach ($request->nilai as $mapel_id => $data) {
            if (isset($data['nilai_akhir'])) {
                \App\Models\RaporAkhir::where('siswa_id', $siswa_id)
                    ->where('mapel_id', $mapel_id)
                    ->where('semester', $semester_int)
                    ->update([
                        'nilai_akhir' => $data['nilai_akhir'],
                        'deskripsi_tertinggi' => $data['deskripsi_tertinggi'] ?? null,
                        'deskripsi_terendah' => $data['deskripsi_terendah'] ?? null,
                    ]);
            }
        }

        return redirect()->back()->with('success', 'Data Nilai Rapor siswa berhasil diperbarui.');
    }

    /**
     * Input Data Ekskul oleh Wali Kelas
     */
    public function ekskul()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->orderBy('nama_lengkap', 'asc')->get();
        $ekskul_list = \App\Models\Ekskul::all();
        
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        $ekskul_nilai = \App\Models\EkskulNilai::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', $semester_int)
                        ->get()->groupBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Ekskul', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'ekskul_list' => $ekskul_list,
            'ekskul_nilai' => $ekskul_nilai
        ]);
    }

    public function storeEkskul(Request $request)
    {
        $request->validate([
            'input_data' => 'required|array'
        ]);

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

        foreach ($request->input_data as $siswa_id => $data) {
            // Delete existing ekskul for this student and semester to allow clean replacement
            \App\Models\EkskulNilai::where('siswa_id', $siswa_id)
                ->where('semester', $semester_int)
                ->delete();

            if (!empty($data) && is_array($data)) {
                foreach ($data as $e) {
                    if (!empty($e['ekskul_id']) && !empty($e['nilai_huruf'])) {
                        \App\Models\EkskulNilai::create([
                            'siswa_id' => $siswa_id,
                            'ekskul_id' => $e['ekskul_id'],
                            'semester' => $semester_int,
                            'nilai_huruf' => $e['nilai_huruf'],
                            'deskripsi_dapodik' => $e['deskripsi_dapodik'] ?? '-',
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Data Nilai Ekskul berhasil disimpan.');
    }
}
