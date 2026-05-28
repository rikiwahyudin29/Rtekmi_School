<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelulusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\NilaiSiswa;
use App\Models\SettingKelulusan;
use App\Models\SuratKeluar;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KelulusanController extends Controller
{
    // =========================================================
    // 1. DAFTAR KELULUSAN
    // =========================================================
    public function index(Request $request)
    {
        $kelasId = $request->input('kelas_id');

        $query = Siswa::with(['kelas.jurusan', 'kelulusan'])
            ->whereHas('kelas', function ($q) {
                $q->where('nama_kelas', 'like', '%XII%')
                  ->orWhere('nama_kelas', 'like', '%12%');
            });

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $siswa = $query->orderBy(Kelas::select('nama_kelas')->whereColumn('tbl_kelas.id', 'tbl_siswa.kelas_id'))
            ->orderBy('nama_lengkap')
            ->get();

        $kelas = Kelas::where('nama_kelas', 'like', '%XII%')
            ->orWhere('nama_kelas', 'like', '%12%')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        return Inertia::render('Admin/Kelulusan/Index', [
            'siswa' => $siswa,
            'kelas' => $kelas
        ]);
    }

    public function simpanMassal(Request $request)
    {
        $dataKelulusan = $request->input('lulus');

        if (empty($dataKelulusan)) {
            return redirect()->back()->with('error', 'Tidak ada data yang diproses!');
        }

        foreach ($dataKelulusan as $siswaId => $val) {
            $status = $val['status'] ?? 'Pending';
            $catatan = $val['catatan'] ?? '';

            Kelulusan::updateOrCreate(
                ['siswa_id' => $siswaId],
                ['status_lulus' => $status, 'catatan' => $catatan]
            );
        }

        return redirect()->route('admin.kelulusan.index')->with('success', 'Status Kelulusan dan Catatan Berhasil Disimpan Massal!');
    }

    // =========================================================
    // 2. SETTING KELULUSAN
    // =========================================================
    public function setting()
    {
        $setting = SettingKelulusan::first();
        if (!$setting) {
            $setting = [
                'tgl_pengumuman' => date('Y-m-d\TH:i'),
                'nomor_surat' => '',
                'titimangsa' => '',
                'pembuka_surat' => '',
                'penutup_surat' => ''
            ];
        }

        return Inertia::render('Admin/Kelulusan/Setting', [
            'setting' => $setting
        ]);
    }

    public function simpanSetting(Request $request)
    {
        $data = $request->validate([
            'tgl_pengumuman' => 'nullable',
            'nomor_surat' => 'nullable',
            'titimangsa' => 'nullable',
            'pembuka_surat' => 'nullable',
            'penutup_surat' => 'nullable',
        ]);

        SettingKelulusan::updateOrCreate(['id' => 1], $data);

        return redirect()->route('admin.kelulusan.setting')->with('success', 'Pengaturan Kelulusan dan Format SKL berhasil disimpan!');
    }

    // =========================================================
    // 3. REKAP NILAI KELULUSAN
    // =========================================================
    public function nilai(Request $request)
    {
        $kelasId = $request->input('kelas_id');

        $query = Siswa::with('kelas')
            ->whereHas('kelas', function ($q) {
                $q->where('nama_kelas', 'like', '%XII%')
                  ->orWhere('nama_kelas', 'like', '%12%');
            });

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $siswa = $query->orderBy(Kelas::select('nama_kelas')->whereColumn('tbl_kelas.id', 'tbl_siswa.kelas_id'))
            ->orderBy('nama_lengkap')
            ->get();

        $kelas = Kelas::where('nama_kelas', 'like', '%XII%')
            ->orWhere('nama_kelas', 'like', '%12%')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        $semuaNilai = NilaiSiswa::all();
        $rekapNilai = [];

        foreach ($semuaNilai as $n) {
            $sid = $n->siswa_id;
            if (!isset($rekapNilai[$sid])) {
                $rekapNilai[$sid] = ['total_mapel' => 0, 'total_rapor' => 0, 'total_us' => 0];
            }

            $rataMapel = (floatval($n->s1) + floatval($n->s2) + floatval($n->s3) + floatval($n->s4) + floatval($n->s5) + floatval($n->s6)) / 6;
            $usMapel = floatval($n->nilai_us);

            $rekapNilai[$sid]['total_mapel'] += 1;
            $rekapNilai[$sid]['total_rapor'] += $rataMapel;
            $rekapNilai[$sid]['total_us'] += $usMapel;
        }

        foreach ($siswa as $s) {
            $sid = $s->id;
            if (isset($rekapNilai[$sid]) && $rekapNilai[$sid]['total_mapel'] > 0) {
                $jml = $rekapNilai[$sid]['total_mapel'];
                $s->rata_rapor = round($rekapNilai[$sid]['total_rapor'] / $jml, 2);
                $s->rata_us = round($rekapNilai[$sid]['total_us'] / $jml, 2);
                $s->nilai_skl = round(($s->rata_rapor * 0.6) + ($s->rata_us * 0.4), 2);
                $s->jml_mapel = $jml;
            } else {
                $s->rata_rapor = 0;
                $s->rata_us = 0;
                $s->nilai_skl = 0;
                $s->jml_mapel = 0;
            }
        }

        return Inertia::render('Admin/Kelulusan/Nilai', [
            'siswa' => $siswa,
            'kelas' => $kelas
        ]);
    }

    // =========================================================
    // 4. DETAIL NILAI & INPUT NILAI MANUAL
    // =========================================================
    public function detailNilai($siswaId)
    {
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($siswaId);
        $jurusanSiswa = $siswa->kelas->id_jurusan;

        $nilai = Mapel::leftJoin('tbl_nilai_siswa as n', function($join) use ($siswaId) {
                $join->on('n.mapel_id', '=', 'tbl_mapel.id')
                     ->where('n.siswa_id', '=', $siswaId);
            })
            ->select('tbl_mapel.nama_mapel', 'tbl_mapel.kelompok', 'n.s1', 'n.s2', 'n.s3', 'n.s4', 'n.s5', 'n.s6', 'n.nilai_us')
            ->where(function($q) use ($jurusanSiswa) {
                $q->where('jurusan_id', '0')
                  ->orWhere('jurusan_id', '')
                  ->orWhereNull('jurusan_id')
                  ->orWhereRaw("FIND_IN_SET(?, jurusan_id) > 0", [$jurusanSiswa]);
            })
            ->orderBy('kelompok', 'asc')
            ->get();

        return Inertia::render('Admin/Kelulusan/DetailNilai', [
            'siswa' => $siswa,
            'nilai' => $nilai
        ]);
    }

    public function inputNilai($siswaId)
    {
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($siswaId);
        $jurusanSiswa = $siswa->kelas->id_jurusan;

        $mapel = Mapel::where(function($q) use ($jurusanSiswa) {
                $q->where('jurusan_id', '0')
                  ->orWhere('jurusan_id', '')
                  ->orWhereNull('jurusan_id')
                  ->orWhereRaw("FIND_IN_SET(?, jurusan_id) > 0", [$jurusanSiswa]);
            })
            ->orderBy('kelompok', 'asc')
            ->get();

        $nilaiExist = NilaiSiswa::where('siswa_id', $siswaId)->get()->keyBy('mapel_id');

        return Inertia::render('Admin/Kelulusan/InputNilai', [
            'siswa' => $siswa,
            'mapel' => $mapel,
            'nilai' => $nilaiExist
        ]);
    }

    public function simpanNilai(Request $request)
    {
        $siswaId = $request->input('siswa_id');
        $dataNilai = $request->input('nilai');

        if (empty($siswaId) || empty($dataNilai)) {
            return redirect()->back()->with('error', 'Gagal! Data nilai kosong.');
        }

        $totalDisimpan = 0;

        foreach ($dataNilai as $mapelId => $val) {
            $s1 = floatval($val['s1'] ?? 0);
            $s2 = floatval($val['s2'] ?? 0);
            $s3 = floatval($val['s3'] ?? 0);
            $s4 = floatval($val['s4'] ?? 0);
            $s5 = floatval($val['s5'] ?? 0);
            $s6 = floatval($val['s6'] ?? 0);
            $us = floatval($val['nilai_us'] ?? 0);

            if ($s1 == 0 && $s2 == 0 && $s3 == 0 && $s4 == 0 && $s5 == 0 && $s6 == 0 && $us == 0) {
                continue;
            }

            NilaiSiswa::updateOrCreate(
                ['siswa_id' => $siswaId, 'mapel_id' => $mapelId],
                ['s1' => $s1, 's2' => $s2, 's3' => $s3, 's4' => $s4, 's5' => $s5, 's6' => $s6, 'nilai_us' => $us]
            );

            $totalDisimpan++;
        }

        return redirect()->route('admin.kelulusan.input_nilai', $siswaId)->with('success', 'Berhasil! ' . $totalDisimpan . ' data mata pelajaran telah disimpan.');
    }

    // =========================================================
    // 5. DOWNLOAD TEMPLATE & IMPORT NILAI
    // =========================================================
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        
        $jurusanRaw = \App\Models\Jurusan::all();
        $mapJurusan = [];
        foreach($jurusanRaw as $jr) { $mapJurusan[$jr->id] = $jr->kode_jurusan; }

        $mapelRaw = Mapel::orderBy('kelompok', 'asc')->get();
        
        $groupedMapel = [];
        foreach($mapelRaw as $m) {
            $jIds = explode(',', $m->jurusan_id ?? '0');
            $jCodes = [];
            if(in_array('0', $jIds)) { $jCodes[] = "ALL"; } 
            else { foreach($jIds as $jid) { if(isset($mapJurusan[$jid])) $jCodes[] = $mapJurusan[$jid]; } }
            
            $m->display_name = $m->nama_mapel . " (" . implode(', ', $jCodes) . ")";
            $groupedMapel[$m->kelompok][] = $m;
        }

        $siswa = Siswa::with('kelas')
            ->whereHas('kelas', function ($q) {
                $q->where('nama_kelas', 'like', '%XII%')->orWhere('nama_kelas', 'like', '%12%');
            })
            ->orderBy(Kelas::select('nama_kelas')->whereColumn('tbl_kelas.id', 'tbl_siswa.kelas_id'))
            ->orderBy('nama_lengkap')
            ->get();

        $colorHeader   = 'FFCFE2F3';
        $colorGroupA  = 'FFD9EAD3';
        $colorGroupB  = 'FFFFFFCC';
        $colorGroupC1 = 'FFFCE5CD';
        $colorGroupC2 = 'FFD9D2E9';
        $colorGroupC3 = 'FFEAD1DC';

        $styleCenter = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ]
        ];
        
        $styleVerticalText = [
            'alignment' => [
                'textRotation' => 90,
                'horizontal'   => Alignment::HORIZONTAL_CENTER,
                'vertical'     => Alignment::VERTICAL_BOTTOM,
            ]
        ];
        
        $styleBorder = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        for ($smt = 1; $smt <= 7; $smt++) {
            if ($smt > 1) $spreadsheet->createSheet();
            $spreadsheet->setActiveSheetIndex($smt - 1);
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheetName = ($smt == 7) ? "Nilai US" : "Smt $smt";
            $sheet->setTitle($sheetName);

            $cellsId = ['A2:A4' => 'NO', 'B2:B4' => 'NISN', 'C2:C4' => 'NAMA SISWA', 'D2:D4' => 'KELAS'];
            foreach($cellsId as $range => $txt) {
                $sheet->mergeCells($range)->setCellValue(explode(':', $range)[0], $txt);
                $sheet->getStyle($range)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorHeader]],
                    'font' => ['bold' => true],
                ]);
            }
            $sheet->getStyle('A2:D4')->applyFromArray($styleCenter);
            $sheet->getStyle('A2:D4')->applyFromArray($styleBorder);

            $currentCol = 5;
            $globalNo = 1;

            foreach ($groupedMapel as $kelompok => $list) {
                $startCol = $currentCol;
                
                $groupColor = 'FFFFFFFF'; 
                if(str_contains($kelompok, 'A.')) $groupColor = $colorGroupA;
                elseif(str_contains($kelompok, 'B.')) $groupColor = $colorGroupB;
                elseif(str_contains($kelompok, 'C1.')) $groupColor = $colorGroupC1;
                elseif(str_contains($kelompok, 'C2.')) $groupColor = $colorGroupC2;
                elseif(str_contains($kelompok, 'C3.')) $groupColor = $colorGroupC3;

                foreach ($list as $mp) {
                    $colChar = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentCol);
                    
                    $sheet->setCellValue($colChar . '1', $mp->id); 
                    $sheet->setCellValue($colChar . '3', $globalNo++); 
                    $sheet->setCellValue($colChar . '4', $mp->display_name); 
                    
                    $sheet->getStyle($colChar . '4')->applyFromArray($styleVerticalText);
                    $sheet->getColumnDimension($colChar)->setWidth(6);
                    
                    $sheet->getStyle($colChar.'2:'.$colChar.'4')->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $groupColor]],
                        'font' => ['bold' => true]
                    ]);
                    $sheet->getStyle($colChar.'2:'.$colChar.'4')->applyFromArray($styleBorder);
                    
                    $currentCol++;
                }
                
                $endCol = $currentCol - 1;
                $startChar = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                $endChar = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCol);
                
                $sheet->mergeCells($startChar . '2:' . $endChar . '2');
                $sheet->setCellValue($startChar . '2', $kelompok);
                $sheet->getStyle($startChar . '2')->applyFromArray($styleCenter);
            }

            $lastColChar = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentCol - 1);
            $sheet->getRowDimension(1)->setVisible(false);
            $sheet->getRowDimension(4)->setRowHeight(210);

            $row = 5; $noUrut = 1; $lastKls = '';
            foreach ($siswa as $s) {
                if ($lastKls != $s->kelas->nama_kelas) { $noUrut = 1; $lastKls = $s->kelas->nama_kelas; }
                $sheet->setCellValue('A' . $row, $noUrut++);
                
                $sheet->setCellValueExplicit('B' . $row, $s->nisn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $sheet->setCellValue('C' . $row, $s->nama_lengkap);
                $sheet->setCellValue('D' . $row, $s->kelas->nama_kelas);
                
                $sheet->getStyle('A'.$row.':'.$lastColChar.$row)->applyFromArray($styleBorder);
                $row++;
            }
            foreach(range('A','D') as $v) { $sheet->getColumnDimension($v)->setAutoSize(true); }
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Nilai_US_dan_Rapor.xlsx"');
        $writer->save('php://output');
        exit;
    }

    public function importNilai(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($file->getPathname());
        $totalSuccess = 0;

        for ($i = 0; $i < 7; $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $data = $sheet->toArray();
            
            $smtCol = ($i == 6) ? "nilai_us" : "s" . ($i + 1);

            if(empty($data[0])) continue;
            $mapelIds = $data[0]; 

            for ($row = 4; $row < count($data); $row++) {
                $nisn = trim($data[$row][1] ?? '');
                if (empty($nisn)) continue;

                $siswa = Siswa::where('nisn', $nisn)->first();
                if (!$siswa) continue;

                for ($col = 4; $col < count($data[$row]); $col++) {
                    $mapelId = $mapelIds[$col];
                    $nilaiVal = floatval($data[$row][$col] ?? 0);
                    if (empty($mapelId)) continue;

                    NilaiSiswa::updateOrCreate(
                        ['siswa_id' => $siswa->id, 'mapel_id' => $mapelId],
                        [$smtCol => $nilaiVal]
                    );
                    $totalSuccess++;
                }
            }
        }
        return redirect()->route('admin.kelulusan.index')->with('success', "Import Selesai! $totalSuccess data berhasil masuk sistem.");
    }

    // =========================================================
    // 6. CETAK TRANSKRIP & SKL
    // =========================================================
    public function cetakTranskrip($siswaId)
    {
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($siswaId);
        $setting = SettingKelulusan::first();
        $sekolah = Sekolah::first();

        $noUrut = sprintf("%03d", $siswa->id);
        $nomorSurat = "421.3/" . $noUrut . "/TRANS-SMK/" . date('Y');

        $cekSurat = SuratKeluar::where('siswa_id', $siswa->id)
            ->where('perihal', 'like', '%Transkrip Nilai%')
            ->first();

        if ($cekSurat) {
            $tokenValidasi = $cekSurat->token_validasi;
            $cekSurat->update([
                'no_surat' => $nomorSurat,
                'tgl_surat' => date('Y-m-d')
            ]);
        } else {
            $tokenValidasi = md5(uniqid(rand(), true));
            SuratKeluar::create([
                'no_surat' => $nomorSurat,
                'siswa_id' => $siswa->id,
                'perihal' => 'Transkrip Nilai',
                'isi_final' => 'Dokumen Transkrip Nilai ini dicetak secara otomatis.',
                'tgl_surat' => date('Y-m-d'),
                'ttd_oleh' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'token_validasi' => $tokenValidasi,
                'status' => 'Disetujui'
            ]);
        }

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode(route('surat.verifikasi', $tokenValidasi));

        $jurusanSiswa = $siswa->kelas->id_jurusan;
        $nilaiMentah = Mapel::leftJoin('tbl_nilai_siswa as n', function($join) use ($siswaId) {
                $join->on('n.mapel_id', '=', 'tbl_mapel.id')
                     ->where('n.siswa_id', '=', $siswaId);
            })
            ->select('tbl_mapel.nama_mapel', 'tbl_mapel.kelompok', 'tbl_mapel.tampil_transkrip', 'n.s1', 'n.s2', 'n.s3', 'n.s4', 'n.s5', 'n.s6', 'n.nilai_us')
            ->where(function($q) {
                $q->where('tbl_mapel.tampil_transkrip', 1)
                  ->orWhere('tbl_mapel.kelompok', 'like', '%C2%')
                  ->orWhere('tbl_mapel.kelompok', 'like', '%C3%');
            })
            ->where(function($q) use ($jurusanSiswa) {
                $q->where('jurusan_id', '0')
                  ->orWhere('jurusan_id', '')
                  ->orWhereNull('jurusan_id')
                  ->orWhereRaw("FIND_IN_SET(?, jurusan_id) > 0", [$jurusanSiswa]);
            })
            ->orderBy('tbl_mapel.kelompok', 'ASC')
            ->orderBy('tbl_mapel.id', 'ASC')
            ->get();

        $nilaiGroup = [];
        $totalRataRaporAll = 0; $totalUsAll = 0; $totalPrestasiAll = 0;
        $jumlahMapel = 0; $jumlahMapelUs = 0;

        $c2 = ['s1'=>0, 's2'=>0, 's3'=>0, 's4'=>0, 's5'=>0, 's6'=>0, 'us'=>0, 'c_s1'=>0, 'c_s2'=>0, 'c_s3'=>0, 'c_s4'=>0, 'c_s5'=>0, 'c_s6'=>0, 'c_us'=>0, 'count_mapel'=>0];
        $c3 = ['s1'=>0, 's2'=>0, 's3'=>0, 's4'=>0, 's5'=>0, 's6'=>0, 'us'=>0, 'c_s1'=>0, 'c_s2'=>0, 'c_s3'=>0, 'c_s4'=>0, 'c_s5'=>0, 'c_s6'=>0, 'c_us'=>0, 'count_mapel'=>0];

        foreach ($nilaiMentah as $n) {
            $s1 = floatval($n->s1 ?? 0); $s2 = floatval($n->s2 ?? 0); $s3 = floatval($n->s3 ?? 0);
            $s4 = floatval($n->s4 ?? 0); $s5 = floatval($n->s5 ?? 0); $s6 = floatval($n->s6 ?? 0);
            $us = floatval($n->nilai_us ?? 0);

            $totalRapor = $s1 + $s2 + $s3 + $s4 + $s5 + $s6;
            $pembagi = 0;
            if ($s1 > 0) $pembagi++; if ($s2 > 0) $pembagi++; if ($s3 > 0) $pembagi++;
            if ($s4 > 0) $pembagi++; if ($s5 > 0) $pembagi++; if ($s6 > 0) $pembagi++;

            $rataRapor = ($pembagi > 0) ? ($totalRapor / $pembagi) : 0;
            $kelompok = strtoupper(trim($n->kelompok));

            if (strpos($kelompok, 'C2') !== false || strpos($kelompok, 'C.2') !== false) {
                if ($s1 > 0) { $c2['s1'] += $s1; $c2['c_s1']++; }
                if ($s2 > 0) { $c2['s2'] += $s2; $c2['c_s2']++; }
                if ($s3 > 0) { $c2['s3'] += $s3; $c2['c_s3']++; }
                if ($s4 > 0) { $c2['s4'] += $s4; $c2['c_s4']++; }
                if ($s5 > 0) { $c2['s5'] += $s5; $c2['c_s5']++; }
                if ($s6 > 0) { $c2['s6'] += $s6; $c2['c_s6']++; }
                if ($us > 0) { $c2['us'] += $us; $c2['c_us']++; }
                $c2['count_mapel']++;
            } elseif (strpos($kelompok, 'C3') !== false || strpos($kelompok, 'C.3') !== false) {
                if ($s1 > 0) { $c3['s1'] += $s1; $c3['c_s1']++; }
                if ($s2 > 0) { $c3['s2'] += $s2; $c3['c_s2']++; }
                if ($s3 > 0) { $c3['s3'] += $s3; $c3['c_s3']++; }
                if ($s4 > 0) { $c3['s4'] += $s4; $c3['c_s4']++; }
                if ($s5 > 0) { $c3['s5'] += $s5; $c3['c_s5']++; }
                if ($s6 > 0) { $c3['s6'] += $s6; $c3['c_s6']++; }
                if ($us > 0) { $c3['us'] += $us; $c3['c_us']++; }
                $c3['count_mapel']++;
            } else {
                if ($n->tampil_transkrip == 1) {
                    if (!isset($nilaiGroup[$kelompok])) $nilaiGroup[$kelompok] = [];
                    $nilaiGroup[$kelompok][] = [
                        'nama_mapel' => $n->nama_mapel,
                        's1' => $s1, 's2' => $s2, 's3' => $s3, 's4' => $s4, 's5' => $s5, 's6' => $s6,
                        'rata_rapor' => round($rataRapor, 2), 'nilai_us' => round($us, 2),
                        'ket' => ($rataRapor > 0 || $us > 0) ? 'Baik' : '-'
                    ];
                    $totalRataRaporAll += $rataRapor; 
                    $jumlahMapel++;
                    
                    if ($us > 0) {
                        $totalUsAll += $us; $jumlahMapelUs++;
                        $totalPrestasiAll += (($rataRapor * 0.6) + ($us * 0.4));
                    } else {
                        $totalPrestasiAll += $rataRapor; 
                    }
                }
            }
        }

        $kategoriKejuruan = 'C1';
        foreach (array_keys($nilaiGroup) as $k) {
            if (strpos(strtoupper($k), 'C') !== false) { $kategoriKejuruan = $k; break; }
        }

        if ($c2['count_mapel'] > 0) {
            $rS1 = ($c2['c_s1'] > 0) ? ($c2['s1'] / $c2['c_s1']) : 0;
            $rS2 = ($c2['c_s2'] > 0) ? ($c2['s2'] / $c2['c_s2']) : 0;
            $rS3 = ($c2['c_s3'] > 0) ? ($c2['s3'] / $c2['c_s3']) : 0;
            $rS4 = ($c2['c_s4'] > 0) ? ($c2['s4'] / $c2['c_s4']) : 0;
            $rS5 = ($c2['c_s5'] > 0) ? ($c2['s5'] / $c2['c_s5']) : 0;
            $rS6 = ($c2['c_s6'] > 0) ? ($c2['s6'] / $c2['c_s6']) : 0;
            
            $pembagiC2 = 0; $totC2 = 0;
            if ($rS1 > 0) { $pembagiC2++; $totC2 += $rS1; }
            if ($rS2 > 0) { $pembagiC2++; $totC2 += $rS2; }
            if ($rS3 > 0) { $pembagiC2++; $totC2 += $rS3; }
            if ($rS4 > 0) { $pembagiC2++; $totC2 += $rS4; }
            if ($rS5 > 0) { $pembagiC2++; $totC2 += $rS5; }
            if ($rS6 > 0) { $pembagiC2++; $totC2 += $rS6; }
            
            $rRapor = ($pembagiC2 > 0) ? ($totC2 / $pembagiC2) : 0;
            $rUs = ($c2['c_us'] > 0) ? ($c2['us'] / $c2['c_us']) : 0;

            $nilaiGroup[$kategoriKejuruan][] = [
                'nama_mapel' => 'Dasar Program Keahlian',
                's1' => round($rS1), 's2' => round($rS2), 's3' => round($rS3), 's4' => round($rS4), 's5' => round($rS5), 's6' => round($rS6),
                'rata_rapor' => round($rRapor, 2), 'nilai_us' => round($rUs, 2), 'ket' => ($rRapor > 0 || $rUs > 0) ? 'Baik' : '-'
            ];
            $totalRataRaporAll += $rRapor; $jumlahMapel++;
            if ($rUs > 0) {
                $totalUsAll += $rUs; $jumlahMapelUs++;
                $totalPrestasiAll += (($rRapor * 0.6) + ($rUs * 0.4));
            } else {
                $totalPrestasiAll += $rRapor;
            }
        }

        if ($c3['count_mapel'] > 0) {
            $rS1 = ($c3['c_s1'] > 0) ? ($c3['s1'] / $c3['c_s1']) : 0;
            $rS2 = ($c3['c_s2'] > 0) ? ($c3['s2'] / $c3['c_s2']) : 0;
            $rS3 = ($c3['c_s3'] > 0) ? ($c3['s3'] / $c3['c_s3']) : 0;
            $rS4 = ($c3['c_s4'] > 0) ? ($c3['s4'] / $c3['c_s4']) : 0;
            $rS5 = ($c3['c_s5'] > 0) ? ($c3['s5'] / $c3['c_s5']) : 0;
            $rS6 = ($c3['c_s6'] > 0) ? ($c3['s6'] / $c3['c_s6']) : 0;
            
            $pembagiC3 = 0; $totC3 = 0;
            if ($rS1 > 0) { $pembagiC3++; $totC3 += $rS1; }
            if ($rS2 > 0) { $pembagiC3++; $totC3 += $rS2; }
            if ($rS3 > 0) { $pembagiC3++; $totC3 += $rS3; }
            if ($rS4 > 0) { $pembagiC3++; $totC3 += $rS4; }
            if ($rS5 > 0) { $pembagiC3++; $totC3 += $rS5; }
            if ($rS6 > 0) { $pembagiC3++; $totC3 += $rS6; }
            
            $rRapor = ($pembagiC3 > 0) ? ($totC3 / $pembagiC3) : 0;
            $rUs = ($c3['c_us'] > 0) ? ($c3['us'] / $c3['c_us']) : 0;

            $nilaiGroup[$kategoriKejuruan][] = [
                'nama_mapel' => 'Kompetensi Keahlian',
                's1' => round($rS1), 's2' => round($rS2), 's3' => round($rS3), 's4' => round($rS4), 's5' => round($rS5), 's6' => round($rS6),
                'rata_rapor' => round($rRapor, 2), 'nilai_us' => round($rUs, 2), 'ket' => ($rRapor > 0 || $rUs > 0) ? 'Baik' : '-'
            ];
            $totalRataRaporAll += $rRapor; $jumlahMapel++;
            if ($rUs > 0) {
                $totalUsAll += $rUs; $jumlahMapelUs++;
                $totalPrestasiAll += (($rRapor * 0.6) + ($rUs * 0.4));
            } else {
                $totalPrestasiAll += $rRapor;
            }
        }

        $finalRataRapor = ($jumlahMapel > 0) ? round($totalRataRaporAll / $jumlahMapel, 2) : 0;
        $finalRataUs    = ($jumlahMapelUs > 0) ? round($totalUsAll / $jumlahMapelUs, 2) : 0;
        $finalRataPrestasi = ($jumlahMapel > 0) ? round($totalPrestasiAll / $jumlahMapel, 2) : 0; 

        $finalGroup = [];
        foreach($nilaiGroup as $key => $val) {
            $newKey = $key; $kUpper = strtoupper($key);
            if(strpos($kUpper, 'A.') !== false || $kUpper == 'A') $newKey = 'Muatan Nasional';
            if(strpos($kUpper, 'B.') !== false || $kUpper == 'B') $newKey = 'Muatan Kewilayahan';
            if(strpos($kUpper, 'C.') !== false || strpos($kUpper, 'C1') !== false || $kUpper == 'C') $newKey = 'Muatan Peminatan Kejuruan';
            $finalGroup[$newKey] = $val;
        }

        return view('kelulusan.cetak_transkrip', [
            'siswa' => $siswa,
            'sekolah' => $sekolah,
            'setting' => $setting,
            'nilai_group' => $finalGroup,
            'rata_rapor' => $finalRataRapor,
            'rata_us' => $finalRataUs,
            'rata_prestasi' => $finalRataPrestasi,
            'nomor_surat' => $nomorSurat,
            'qr_url' => $qrUrl,
            'token_validasi' => $tokenValidasi
        ]);
    }

    public function cetakSkl($siswaId)
    {
        $siswa = Siswa::with(['kelas.jurusan', 'kelulusan'])->findOrFail($siswaId);
        if (!$siswa->kelulusan || $siswa->kelulusan->status_lulus == 'Pending') {
            return redirect()->back()->with('error', 'Status kelulusan siswa ini masih Pending!');
        }

        $setting = SettingKelulusan::first();
        $sekolah = Sekolah::first();

        $noUrut = sprintf("%03d", $siswa->id);
        $nomorSurat = str_replace('[NO_URUT]', $noUrut, $setting->nomor_surat ?? '');

        $cekSurat = SuratKeluar::where('siswa_id', $siswa->id)
            ->where('perihal', 'like', '%Surat Keterangan Lulus%')
            ->first();

        if ($cekSurat) {
            $tokenValidasi = $cekSurat->token_validasi;
            $cekSurat->update([
                'no_surat' => $nomorSurat,
                'tgl_surat' => date('Y-m-d')
            ]);
        } else {
            $tokenValidasi = md5(uniqid(rand(), true));
            SuratKeluar::create([
                'no_surat' => $nomorSurat,
                'siswa_id' => $siswa->id,
                'perihal' => 'Surat Keterangan Lulus',
                'isi_final' => 'Dokumen SKL ini dicetak secara otomatis oleh Sistem Manajemen Kelulusan.',
                'tgl_surat' => date('Y-m-d'),
                'ttd_oleh' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'token_validasi' => $tokenValidasi,
                'status' => 'Disetujui'
            ]);
        }

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode(route('surat.verifikasi', $tokenValidasi));

        $jurusanSiswa = $siswa->kelas->id_jurusan;
        $nilaiMentah = Mapel::leftJoin('tbl_nilai_siswa as n', function($join) use ($siswaId) {
                $join->on('n.mapel_id', '=', 'tbl_mapel.id')
                     ->where('n.siswa_id', '=', $siswaId);
            })
            ->select('tbl_mapel.nama_mapel', 'tbl_mapel.kelompok', 'tbl_mapel.tampil_skl', 'n.s1', 'n.s2', 'n.s3', 'n.s4', 'n.s5', 'n.s6', 'n.nilai_us')
            ->where(function($q) {
                $q->where('tbl_mapel.tampil_skl', 1)
                  ->orWhere('tbl_mapel.kelompok', 'like', '%C2%')
                  ->orWhere('tbl_mapel.kelompok', 'like', '%C3%');
            })
            ->where(function($q) use ($jurusanSiswa) {
                $q->where('jurusan_id', '0')
                  ->orWhere('jurusan_id', '')
                  ->orWhereNull('jurusan_id')
                  ->orWhereRaw("FIND_IN_SET(?, jurusan_id) > 0", [$jurusanSiswa]);
            })
            ->orderBy('tbl_mapel.kelompok', 'ASC')
            ->orderBy('tbl_mapel.id', 'ASC')
            ->get();

        $nilaiGroup = [];
        $totalSemuaNilai = 0; $jumlahMapel = 0;
        $c2TotalNilai = 0; $c2Jumlah = 0;
        $c3TotalNilai = 0; $c3Jumlah = 0;

        foreach ($nilaiMentah as $n) {
            $s1 = floatval($n->s1 ?? 0); $s2 = floatval($n->s2 ?? 0); $s3 = floatval($n->s3 ?? 0);
            $s4 = floatval($n->s4 ?? 0); $s5 = floatval($n->s5 ?? 0); $s6 = floatval($n->s6 ?? 0);
            $us = floatval($n->nilai_us ?? 0);

            $totalRapor = $s1 + $s2 + $s3 + $s4 + $s5 + $s6;
            $pembagi = 0;
            if ($s1 > 0) $pembagi++; if ($s2 > 0) $pembagi++; if ($s3 > 0) $pembagi++;
            if ($s4 > 0) $pembagi++; if ($s5 > 0) $pembagi++; if ($s6 > 0) $pembagi++;

            $rataRapor = ($pembagi > 0) ? ($totalRapor / $pembagi) : 0;
            $nilaiSkl = ($us > 0) ? (($rataRapor * 0.6) + ($us * 0.4)) : $rataRapor;
            $nilaiAkhir = round($nilaiSkl, 2);

            $kelompok = strtoupper(trim($n->kelompok));

            if (strpos($kelompok, 'C2') !== false || strpos($kelompok, 'C.2') !== false) {
                $c2TotalNilai += $nilaiAkhir; $c2Jumlah++;
            } elseif (strpos($kelompok, 'C3') !== false || strpos($kelompok, 'C.3') !== false) {
                $c3TotalNilai += $nilaiAkhir; $c3Jumlah++;
            } else {
                if ($n->tampil_skl == 1) {
                    if (!isset($nilaiGroup[$kelompok])) $nilaiGroup[$kelompok] = [];
                    $nilaiGroup[$kelompok][] = ['nama_mapel' => $n->nama_mapel, 'nilai_akhir' => $nilaiAkhir];
                    $totalSemuaNilai += $nilaiAkhir; $jumlahMapel++;
                }
            }
        }

        $kategoriKejuruan = 'C1';
        foreach (array_keys($nilaiGroup) as $k) {
            if (strpos(strtoupper($k), 'C') !== false) { $kategoriKejuruan = $k; break; }
        }

        if ($c2Jumlah > 0) {
            $rataC2 = round($c2TotalNilai / $c2Jumlah, 2);
            $nilaiGroup[$kategoriKejuruan][] = ['nama_mapel' => 'Dasar Program Keahlian', 'nilai_akhir' => $rataC2];
            $totalSemuaNilai += $rataC2; $jumlahMapel++;
        }
        if ($c3Jumlah > 0) {
            $rataC3 = round($c3TotalNilai / $c3Jumlah, 2);
            $nilaiGroup[$kategoriKejuruan][] = ['nama_mapel' => 'Kompetensi Keahlian', 'nilai_akhir' => $rataC3];
            $totalSemuaNilai += $rataC3; $jumlahMapel++;
        }

        $rataRataKeseluruhan = ($jumlahMapel > 0) ? round($totalSemuaNilai / $jumlahMapel, 2) : 0;

        $finalGroup = [];
        foreach($nilaiGroup as $key => $val) {
            $newKey = $key;
            $kUpper = strtoupper($key);
            if(strpos($kUpper, 'A.') !== false || $kUpper == 'A') $newKey = 'Muatan Nasional';
            if(strpos($kUpper, 'B.') !== false || $kUpper == 'B') $newKey = 'Muatan Kewilayahan';
            if(strpos($kUpper, 'C.') !== false || strpos($kUpper, 'C1') !== false || $kUpper == 'C') $newKey = 'Muatan Peminatan Kejuruan';
            $finalGroup[$newKey] = $val;
        }

        return view('kelulusan.cetak_skl', [
            'siswa' => $siswa,
            'sekolah' => $sekolah,
            'setting' => $setting,
            'nilai_group' => $finalGroup,
            'rata_rata' => $rataRataKeseluruhan,
            'nomor_surat' => $nomorSurat,
            'qr_url' => $qrUrl,
            'token_validasi' => $tokenValidasi
        ]);
    }
}
