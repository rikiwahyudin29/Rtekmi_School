<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\RaporAkhir;
use App\Models\RaporKehadiran;
use App\Models\RaporCatatanWali;
use App\Models\RaporPkl;
use App\Models\P5Nilai;
use App\Models\UkkNilai;
use App\Models\Kelas;
use App\Models\EkskulNilai;
use App\Models\Sekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;

class CetakRaporController extends Controller
{
    /**
     * Cetak Cover Depan Rapor
     */
    public function cetakCover($id)
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($id);
        $sekolah = Sekolah::first();
        
        // Di aplikasi riil, akan menggunakan view HTML khusus cetak yang di-styling mirip eRapor.
        // Bisa menggunakan dompdf atau print window javascript.
        return view('rapor.cetak_cover', compact('siswa', 'sekolah'));
    }

    /**
     * Cetak Halaman Nilai Rapor (Kurikulum Merdeka)
    /**
     * Cetak Nilai via API (tanpa login, verifikasi hash)
     */
    public function cetakNilaiApi($id, $hash)
    {
        $expectedHash = md5($id . env('API_SECRET_KEY') . 'raport');
        if ($hash !== $expectedHash) {
            abort(403, 'Akses ditolak. Link tidak valid atau kedaluwarsa.');
        }
        return $this->cetakNilai($id);
    }

    /**
     * Menampilkan halaman cetak rapor nilai
     */
    public function cetakNilai($id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $tanggal_rapor = Carbon::now()->translatedFormat('d F Y');
        $semester_int = ($tahun_ajaran && $tahun_ajaran->semester === 'Genap') ? 2 : 1;

        $siswa = Siswa::with(['kelas.waliKelas', 'jurusan'])->findOrFail($id);
        $rapor_akhir = RaporAkhir::with('mapel')
            ->where('siswa_id', $id)
            ->where('semester', $semester_int)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('mapel_id')
            ->values();
        $kehadiran = RaporKehadiran::where('siswa_id', $id)
            ->where('semester', $semester_int)
            ->first() ?? (object) ['sakit' => 0, 'izin' => 0, 'tanpa_keterangan' => 0];
        $catatan = RaporCatatanWali::where('siswa_id', $id)->where('semester', $semester_int)->first();
        $pkl = RaporPkl::with('dudi')->where('siswa_id', $id)->orderBy('updated_at', 'desc')->get()->unique('dudi_id')->values();
        $semester_str = $tahun_ajaran ? ($tahun_ajaran->semester . ' ' . $tahun_ajaran->tahun_ajaran) : '-';
        $ekskul = EkskulNilai::with('ekskul')->where('siswa_id', $id)->where('semester', $semester_str)->get();
        $kenaikan = \App\Models\KenaikanKelas::with('kelasTujuan')->where('siswa_id', $id)->first();

        return view('rapor.cetak_nilai', compact('siswa', 'rapor_akhir', 'kehadiran', 'catatan', 'pkl', 'ekskul', 'sekolah', 'tahun_ajaran', 'tanggal_rapor', 'kenaikan'));
    }

    /**
     * Cetak Rapor Projek Penguatan Profil Pelajar Pancasila (P5)
     */
    public function cetakP5($id)
    {
        $siswa = Siswa::with(['kelas'])->findOrFail($id);
        $nilai_p5 = P5Nilai::with(['subElemen.elemen.dimensi', 'kelompok.projek.tema'])
                        ->where('siswa_id', $id)
                        ->get();
        
        return view('rapor.cetak_p5', compact('siswa', 'nilai_p5'));
    }

    /**
     * Cetak Sertifikat UKK (Skill Passport)
     */
    public function cetakUkk($id)
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($id);
        $ukk = UkkNilai::with(['paket.jurusan', 'asesorInternal', 'asesorEksternal'])
                        ->where('siswa_id', $id)
                        ->first();
                        
        return view('rapor.cetak_ukk', compact('siswa', 'ukk'));
    }

    /**
     * Cetak Leger Kelas
     */
    public function cetakLeger($kelas_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        $siswa = Siswa::where('kelas_id', $kelas_id)->get();
        $rapor_akhir = RaporAkhir::with('mapel')
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique(function ($item) {
                return $item->siswa_id . '-' . $item->mapel_id;
            })
            ->values();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran && $tahun_ajaran->semester === 'Genap') ? 2 : 1;
        $kehadiran = RaporKehadiran::whereIn('siswa_id', $siswa->pluck('id'))
            ->where('semester', $semester_int)
            ->get();
        
        // Calculate Peringkat
        $peringkat_data = [];
        foreach($siswa as $s) {
            $total = $rapor_akhir->where('siswa_id', $s->id)->sum('nilai_akhir');
            $count = $rapor_akhir->where('siswa_id', $s->id)->count();
            $rata = $count > 0 ? $total / $count : 0;
            $peringkat_data[$s->id] = [
                'total' => $total,
                'rata' => $rata,
                'rank' => 0
            ];
        }

        // Sort by total descending
        $sorted = collect($peringkat_data)->sortByDesc('total');
        
        $rank = 1;
        $prevTotal = -1;
        $prevRank = 1;
        foreach($sorted as $siswa_id => $data) {
            if ($data['total'] == $prevTotal) {
                $peringkat_data[$siswa_id]['rank'] = $prevRank;
            } else {
                $peringkat_data[$siswa_id]['rank'] = $rank;
                $prevRank = $rank;
            }
            $prevTotal = $data['total'];
            $rank++;
        }

        // Sort Siswa by Rank
        $siswa = $siswa->sortBy(function($s) use ($peringkat_data) {
            return $peringkat_data[$s->id]['rank'] ?? 999;
        })->values();

        return view('rapor.cetak_leger', compact('kelas', 'siswa', 'rapor_akhir', 'kehadiran', 'peringkat_data'));
    }

    /**
     * Export Leger ke Excel
     */
    public function exportLegerExcel($kelas_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        $siswa = Siswa::where('kelas_id', $kelas_id)->get();
        $rapor_akhir = RaporAkhir::with('mapel')
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique(function ($item) {
                return $item->siswa_id . '-' . $item->mapel_id;
            })
            ->values();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran && $tahun_ajaran->semester === 'Genap') ? 2 : 1;
        $kehadiran = RaporKehadiran::whereIn('siswa_id', $siswa->pluck('id'))
            ->where('semester', $semester_int)
            ->get();
        
        // Dapatkan Mapel Unik
        $mapels = $rapor_akhir->pluck('mapel')->unique('id')->filter()->sortBy(function($m) { 
            $u = (int) ($m->urutan ?? 0); 
            return $u === 0 ? 999 : $u; 
        })->values();

        // Hitung Peringkat
        $peringkat_data = [];
        foreach($siswa as $s) {
            $total = $rapor_akhir->where('siswa_id', $s->id)->sum('nilai_akhir');
            $count = $rapor_akhir->where('siswa_id', $s->id)->count();
            $rata = $count > 0 ? $total / $count : 0;
            $peringkat_data[$s->id] = [
                'total' => $total,
                'rata' => $rata,
                'rank' => 0
            ];
        }

        $sorted = collect($peringkat_data)->sortByDesc('total');
        $rank = 1;
        $prevTotal = -1;
        $prevRank = 1;
        foreach($sorted as $siswa_id => $data) {
            if ($data['total'] == $prevTotal) {
                $peringkat_data[$siswa_id]['rank'] = $prevRank;
            } else {
                $peringkat_data[$siswa_id]['rank'] = $rank;
                $prevRank = $rank;
            }
            $prevTotal = $data['total'];
            $rank++;
        }

        // Sort Siswa by Rank
        $siswa = $siswa->sortBy(function($s) use ($peringkat_data) {
            return $peringkat_data[$s->id]['rank'] ?? 999;
        })->values();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leger ' . str_replace(['/', '\\', '*', '?', '[', ']'], '_', $kelas->nama_kelas));

        // Header Style
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]
        ];

        // Set Headers
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NIS/NISN');
        $sheet->setCellValue('C1', 'NAMA SISWA');
        $sheet->setCellValue('D1', 'L/P');
        
        $col = 'E';
        foreach($mapels as $mapel) {
            $sheet->setCellValue($col . '1', $mapel->singkatan ?? substr($mapel->nama_mapel, 0, 10));
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        
        $colTotal = $col;
        $sheet->setCellValue($colTotal . '1', 'JUMLAH');
        $col++;
        $colRata = $col;
        $sheet->setCellValue($colRata . '1', 'RATA-RATA');
        $col++;
        $colRank = $col;
        $sheet->setCellValue($colRank . '1', 'RANK');
        $col++;
        
        $sheet->setCellValue($col . '1', 'S');
        $col++;
        $sheet->setCellValue($col . '1', 'I');
        $col++;
        $sheet->setCellValue($col . '1', 'A');
        
        $lastCol = $col;
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Fill Data
        $row = 2;
        foreach($siswa as $index => $s) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, ($s->nis ?? '-') . '/' . $s->nisn);
            $sheet->setCellValue('C' . $row, $s->nama_lengkap);
            $sheet->setCellValue('D' . $row, $s->jk == 'P' || $s->jenis_kelamin == 'P' ? 'P' : 'L');
            
            $col = 'E';
            foreach($mapels as $mapel) {
                $nilai = $rapor_akhir->where('siswa_id', $s->id)->where('mapel_id', $mapel->id)->first();
                $sheet->setCellValue($col . $row, $nilai ? $nilai->nilai_akhir : '');
                $col++;
            }
            
            $sheet->setCellValue($colTotal . $row, $peringkat_data[$s->id]['total']);
            $sheet->setCellValue($colRata . $row, round($peringkat_data[$s->id]['rata'], 2));
            $sheet->setCellValue($colRank . $row, $peringkat_data[$s->id]['rank']);
            
            $absen = $kehadiran->where('siswa_id', $s->id)->first();
            $col = $colRank;
            $col++;
            $sheet->setCellValue($col . $row, $absen ? $absen->sakit : 0);
            $col++;
            $sheet->setCellValue($col . $row, $absen ? $absen->izin : 0);
            $col++;
            $sheet->setCellValue($col . $row, $absen ? $absen->tanpa_keterangan : 0);

            $row++;
        }

        $bodyStyle = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A2:' . $lastCol . ($row - 1))->applyFromArray($bodyStyle);

        // Auto size for main columns
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Leger_Kelas_' . str_replace([' ', '/'], '_', $kelas->nama_kelas) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Cetak Buku Induk / Transkrip
     */
    public function cetakPelengkap($id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($id);
        
        return view('rapor.cetak_pelengkap', compact('siswa', 'sekolah', 'tahun_ajaran'));
    }

    /**
     * Cetak Cover Depan Rapor Masal
     */
    public function cetakCoverMasal($kelas_id)
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->where('kelas_id', $kelas_id)->get();
        $sekolah = Sekolah::first();
        
        return view('rapor.masal_cover', compact('siswas', 'sekolah'));
    }

    /**
     * Cetak Pelengkap Rapor Masal
     */
    public function cetakPelengkapMasal($kelas_id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $siswas = Siswa::with(['kelas', 'jurusan'])->where('kelas_id', $kelas_id)->get();
        
        return view('rapor.masal_pelengkap', compact('siswas', 'sekolah', 'tahun_ajaran'));
    }

    /**
     * Cetak Nilai Rapor Masal
     */
    public function cetakNilaiMasal($kelas_id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $tanggal_rapor = Carbon::now()->translatedFormat('d F Y');
        $semester_int = ($tahun_ajaran && $tahun_ajaran->semester === 'Genap') ? 2 : 1;

        $siswas = Siswa::with(['kelas.waliKelas', 'jurusan'])->where('kelas_id', $kelas_id)->get();
        
        // Fetch all necessary relations for these students
        $rapor_akhir_all = collect();
        $kehadiran_all = collect();
        $catatan_all = collect();
        $pkl_all = collect();
        $ekskul_all = collect();
        $kenaikan_all = collect();
        
        foreach($siswas as $siswa) {
            $rapor_akhir_all[$siswa->id] = RaporAkhir::with('mapel')
                ->where('siswa_id', $siswa->id)
                ->where('semester', $semester_int)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->unique('mapel_id')
                ->values();
            $kehadiran_all[$siswa->id] = RaporKehadiran::where('siswa_id', $siswa->id)->where('semester', $semester_int)->first() ?? (object) ['sakit' => 0, 'izin' => 0, 'tanpa_keterangan' => 0];
            $catatan_all[$siswa->id] = RaporCatatanWali::where('siswa_id', $siswa->id)->where('semester', $semester_int)->first();
            $pkl_all[$siswa->id] = RaporPkl::with('dudi')->where('siswa_id', $siswa->id)->orderBy('updated_at', 'desc')->get()->unique('dudi_id')->values();
            
            $semester_str = $tahun_ajaran ? ($tahun_ajaran->semester . ' ' . $tahun_ajaran->tahun_ajaran) : '-';
            $ekskul_all[$siswa->id] = EkskulNilai::with('ekskul')->where('siswa_id', $siswa->id)->where('semester', $semester_str)->get();
            
            $kenaikan_all[$siswa->id] = \App\Models\KenaikanKelas::with('kelasTujuan')->where('siswa_id', $siswa->id)->first();
        }

        return view('rapor.masal_nilai', compact('siswas', 'rapor_akhir_all', 'kehadiran_all', 'catatan_all', 'pkl_all', 'ekskul_all', 'sekolah', 'tahun_ajaran', 'tanggal_rapor', 'kenaikan_all'));
    }
}
