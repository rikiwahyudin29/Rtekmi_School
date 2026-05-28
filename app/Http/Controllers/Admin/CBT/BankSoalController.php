<?php

namespace App\Http\Controllers\Admin\CBT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BankSoalController extends Controller
{
    public function index(Request $request)
    {
        $query = BankSoal::with(['mapel', 'creator.guru'])->orderBy('id', 'desc');

        $userRoles = session('roles', []);
        $isAdmin = in_array('admin', $userRoles) || in_array('superadmin', $userRoles) || in_array('kurikulum', $userRoles) || in_array('kepsek', $userRoles);
        
        if (!$isAdmin && in_array('guru', $userRoles)) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('id_mapel')) {
            $query->where('mapel_id', $request->id_mapel);
        }

        if ($request->filled('id_guru')) {
            $query->where('user_id', $request->id_guru);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('kode', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_mapel', 'like', '%' . $request->search . '%');
            });
        }

        $bankSoalData = $query->paginate(10)->withQueryString();
        
        // Transform data to include count of questions
        $bankSoalData->getCollection()->transform(function ($item) {
            $item->jumlah_soal = \App\Models\SoalData::where('bank_id', $item->id)->count();
            return $item;
        });
        
        $mapel = Mapel::orderBy('nama_mapel', 'asc')->get();
        
        // Ambil data user yang memiliki role guru atau admin untuk filter guru
        $guru = User::whereHas('roles', function($q) {
            $q->where('role_key', 'guru');
        })->orWhere('role', 'guru')->with('guru')->orderBy('nama_lengkap', 'asc')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'nama_lengkap' => $user->guru->nama_lengkap ?? $user->nama_lengkap
            ];
        });

        return Inertia::render('Admin/CBT/BankSoal/Index', [
            'bankSoal' => $bankSoalData,
            'mapel' => $mapel,
            'guru' => $guru,
            'filters' => $request->only(['id_mapel', 'id_guru', 'search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:m_banksoal,kode',
            'mapel_id' => 'required|exists:tbl_mapel,id',
        ]);

        $mapel = Mapel::findOrFail($request->mapel_id);

        BankSoal::create([
            'kode' => strtoupper($request->kode),
            'mapel_id' => $request->mapel_id,
            'nama_mapel' => $mapel->nama_mapel,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Bank Soal berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|unique:m_banksoal,kode,' . $id,
            'mapel_id' => 'required|exists:tbl_mapel,id',
        ]);

        $mapel = Mapel::findOrFail($request->mapel_id);
        $bankSoal = BankSoal::findOrFail($id);

        $bankSoal->update([
            'kode' => strtoupper($request->kode),
            'mapel_id' => $request->mapel_id,
            'nama_mapel' => $mapel->nama_mapel,
        ]);

        return redirect()->back()->with('success', 'Bank Soal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $bankSoal = BankSoal::findOrFail($id);
        $bankSoal->delete();

        return redirect()->back()->with('success', 'Bank Soal berhasil dihapus.');
    }

    public function soal($id)
    {
        $bankSoal = BankSoal::with(['mapel', 'creator.guru'])->findOrFail($id);
        
        return Inertia::render('Admin/CBT/BankSoal/Editor', [
            'bankSoal' => $bankSoal,
            'baseUrl' => url('/')
        ]);
    }

    public function getSoal($id)
    {
        $soals = \App\Models\SoalData::with(['opsi', 'couples'])
            ->where('bank_id', $id)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $soals
        ]);
    }

    public function saveSoal(Request $request, $id)
    {
        if (!BankSoal::find($id)) {
            return response()->json(['status' => 'error', 'message' => 'Bank Soal tidak ditemukan'], 404);
        }

        $dataSoals = $request->input('soals', []);

        DB::beginTransaction();
        try {
            $needOption = [1, 3, 4, 6];
            $needQuestion = [4];
            
            $keepSoalIds = [];
            $newIDs = ['soal' => [], 'opsi' => [], 'couple' => []];

            foreach ($dataSoals as $k => $soalData) {
                $nomor = $k + 1;
                $isNew = $soalData['isnew'] ?? false;
                $couples = $soalData['couples'] ?? [];
                $options = $soalData['options'] ?? [];
                $shortentry = isset($soalData['shortentry']) ? trim($soalData['shortentry']) : '';

                if (in_array($soalData['jenis_soal'], $needQuestion) && count($couples) == 0) {
                    throw new \Exception("Minimal 1 Jawaban Pada Soal Nomor $nomor");
                }
                if (in_array($soalData['jenis_soal'], $needOption) && count($options) == 0) {
                    throw new \Exception("Minimal 1 Opsi Pada Soal Nomor $nomor");
                }

                $questionText = str_replace(["{…}", "{&hellip;}"], ["{...}", "{...}"], $soalData['question'] ?? '');

                // 1. Save or Update SoalData
                if ($isNew || !isset($soalData['id']) || empty($soalData['id']) || strpos($soalData['id'], 'new') !== false) {
                    $soal = \App\Models\SoalData::create([
                        'bank_id' => $id,
                        'true_default_point' => $soalData['true_default_point'] ?? 1,
                        'false_default_point' => $soalData['false_default_point'] ?? 0,
                        'question' => $questionText,
                        'jenis_soal' => $soalData['jenis_soal'],
                        'shortentry' => $shortentry,
                    ]);
                    $oldId = $soalData['id'] ?? null;
                    $soalId = $soal->id;
                    if ($oldId) {
                        $newIDs['soal'][$oldId] = $soalId;
                    }
                } else {
                    $soal = \App\Models\SoalData::findOrFail($soalData['id']);
                    $soal->update([
                        'true_default_point' => $soalData['true_default_point'] ?? 1,
                        'false_default_point' => $soalData['false_default_point'] ?? 0,
                        'question' => $questionText,
                        'jenis_soal' => $soalData['jenis_soal'],
                        'shortentry' => $shortentry,
                    ]);
                    $soalId = $soal->id;
                }
                $keepSoalIds[] = $soalId;

                // 2. Save or Update SoalDataCouple (matching questions)
                $keepCoupleIds = [];
                foreach ($couples as $vc) {
                    $vcIsNew = $vc['isnew'] ?? false;
                    if ($vcIsNew || !isset($vc['id']) || empty($vc['id']) || strpos($vc['id'], 'couple') !== false) {
                        $coupleObj = \App\Models\SoalDataCouple::create([
                            'soal_id' => $soalId,
                            'body' => $vc['body'],
                        ]);
                        $oldCoupleId = $vc['id'] ?? null;
                        $coupleId = $coupleObj->id;
                        if ($oldCoupleId) {
                            $newIDs['couple'][$oldCoupleId] = $coupleId;
                        }
                    } else {
                        $coupleObj = \App\Models\SoalDataCouple::findOrFail($vc['id']);
                        $coupleObj->update([
                            'body' => $vc['body'],
                        ]);
                        $coupleId = $coupleObj->id;
                    }
                    $keepCoupleIds[] = $coupleId;
                }
                
                // Hapus couple yang tidak digunakan lagi
                \App\Models\SoalDataCouple::where('soal_id', $soalId)
                    ->whereNotIn('id', $keepCoupleIds)
                    ->delete();

                // 3. Save or Update SoalOpsi (options)
                $keepOpsiIds = [];
                foreach ($options as $vo) {
                    $voIsNew = $vo['isnew'] ?? false;
                    
                    // Map couple ID jika dipetakan ke couple yang baru dibuat
                    $coupleId = $vo['soal_couple_id'] ?? null;
                    if ($coupleId && isset($newIDs['couple'][$coupleId])) {
                        $coupleId = $newIDs['couple'][$coupleId];
                    }

                    if ($voIsNew || !isset($vo['id']) || empty($vo['id']) || strpos($vo['id'], 'opt') !== false) {
                        $opsiObj = \App\Models\SoalOpsi::create([
                            'soal_id' => $soalId,
                            'soal_couple_id' => $coupleId,
                            'body' => $vo['body'],
                            'is_key' => !empty($vo['is_key']) ? 1 : 0,
                        ]);
                        $oldOpsiId = $vo['id'] ?? null;
                        $opsiId = $opsiObj->id;
                        if ($oldOpsiId) {
                            $newIDs['opsi'][$oldOpsiId] = $opsiId;
                        }
                    } else {
                        $opsiObj = \App\Models\SoalOpsi::findOrFail($vo['id']);
                        $opsiObj->update([
                            'soal_couple_id' => $coupleId,
                            'body' => $vo['body'],
                            'is_key' => !empty($vo['is_key']) ? 1 : 0,
                        ]);
                        $opsiId = $opsiObj->id;
                    }
                    $keepOpsiIds[] = $opsiId;
                }
                
                // Hapus opsi yang tidak digunakan lagi
                \App\Models\SoalOpsi::where('soal_id', $soalId)
                    ->whereNotIn('id', $keepOpsiIds)
                    ->delete();
            }

            // Hapus soal yang tidak ada di payload
            \App\Models\SoalData::where('bank_id', $id)
                ->whereNotIn('id', $keepSoalIds)
                ->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menyimpan',
                'newID' => $newIDs
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplateExcel()
    {
        // Require phpspreadsheet (pastikan sudah di-install)
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return redirect()->back()->with('error', 'Library PhpSpreadsheet belum diinstall. Jalankan: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. SET HEADER (Baris Pertama)
        $sheet->setCellValue('A1', 'Pertanyaan');
        $sheet->setCellValue('B1', 'Tipe (PG/ESSAY)');
        $sheet->setCellValue('C1', 'Opsi A');
        $sheet->setCellValue('D1', 'Opsi B');
        $sheet->setCellValue('E1', 'Opsi C');
        $sheet->setCellValue('F1', 'Opsi D');
        $sheet->setCellValue('G1', 'Opsi E');
        $sheet->setCellValue('H1', 'Kunci (A/B/C/D/E)');

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        // 2. BERI CONTOH DATA
        $sheet->setCellValue('A2', 'Siapakah Presiden pertama Republik Indonesia?');
        $sheet->setCellValue('B2', 'PG');
        $sheet->setCellValue('C2', 'Soeharto');
        $sheet->setCellValue('D2', 'B.J. Habibie');
        $sheet->setCellValue('E2', 'Ir. Soekarno');
        $sheet->setCellValue('F2', 'Joko Widodo');
        $sheet->setCellValue('G2', 'Megawati');
        $sheet->setCellValue('H2', 'C'); 

        $sheet->setCellValue('A3', 'Jelaskan apa yang dimaksud dengan fotosintesis!');
        $sheet->setCellValue('B3', 'ESSAY');

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = 'Template_Import_Soal.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importSoalExcel(Request $request, $idBank)
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Reader\Xlsx')) {
            return redirect()->back()->with('error', 'Library PhpSpreadsheet belum diinstall. Jalankan: composer require phpoffice/phpspreadsheet');
        }

        $request->validate([
            'file_excel' => 'required|mimes:xls,xlsx|max:10240'
        ]);

        $file = $request->file('file_excel');

        try {
            $ext = $file->getClientOriginalExtension();
            $reader = ('xls' == $ext) ? new \PhpOffice\PhpSpreadsheet\Reader\Xls() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet()->toArray();

            $jumlahSukses = 0;
            DB::beginTransaction();

            foreach ($sheet as $key => $row) {
                if ($key == 0) continue; // Skip Header

                $pertanyaan = $row[0] ?? '';
                $tipeRaw    = strtoupper($row[1] ?? 'PG');
                
                if (empty($pertanyaan)) continue;

                // Map CI4 Tipe ke Laravel Tipe (1=PG, 2=Esai, 3=PG Kompleks, 4=Menjodohkan, 5=Isian Singkat, 6=Benar/Salah)
                $jenis_soal = 1; // Default PG
                if ($tipeRaw == 'ESSAY') $jenis_soal = 2; // Map essay to Esai

                // 1. Simpan Soal
                $soal = \App\Models\SoalData::create([
                    'bank_id'      => $idBank,
                    'jenis_soal'   => $jenis_soal,
                    'question'     => $pertanyaan,
                    'true_default_point'  => 1,
                    'false_default_point' => 0
                ]);
                $idSoal = $soal->id;

                // 2. Simpan Opsi (Khusus PG)
                if ($jenis_soal == 1) {
                    $opsiArr = [
                        'A' => $row[2] ?? '', 'B' => $row[3] ?? '', 'C' => $row[4] ?? '', 'D' => $row[5] ?? '', 'E' => $row[6] ?? ''
                    ];
                    $kunci = strtoupper(trim($row[7] ?? ''));

                    foreach ($opsiArr as $kode => $teks) {
                        if (!empty($teks)) {
                            \App\Models\SoalOpsi::create([
                                'soal_id'   => $idSoal,
                                'body'      => $teks,
                                'is_key'    => ($kode == $kunci) ? 1 : 0
                            ]);
                        }
                    }
                }
                $jumlahSukses++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Berhasil import $jumlahSukses soal dari Excel.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'File tidak valid atau error parsing: ' . $e->getMessage());
        }
    }

    public function downloadTemplateWord()
    {
        if (!class_exists('\PhpOffice\PhpWord\PhpWord')) {
            return redirect()->back()->with('error', 'Library PhpWord belum diinstall. Jalankan: composer require phpoffice/phpword');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $headerStyle = ['bold' => true, 'size' => 14];
        $boldStyle = ['bold' => true];
        $normalStyle = ['size' => 11];

        $section->addText('TEMPLATE IMPORT SOAL (Q-FORMAT)', $headerStyle);
        $section->addText('Panduan:', $boldStyle);
        $section->addText('1. Jangan hapus kode (Q>:, A>:, K>:).', $normalStyle);
        $section->addText('2. Gambar WAJIB setting "In Line with Text".', $normalStyle);
        $section->addText('3. PT = Bobot Nilai Benar (Opsional, default 1).', $normalStyle);
        $section->addText('4. PF = Bobot Nilai Salah (Opsional, default 0, misal -1).', $normalStyle);
        $section->addTextBreak(1);

        $section->addText('--- CONTOH 1: PILIHAN GANDA (PG) ---', ['bold' => true, 'color' => '0000FF']);
        $section->addText('Q>: Siapa Presiden pertama Indonesia?');
        $section->addText('A>: Soeharto');
        $section->addText('A>: B.J. Habibie');
        $section->addText('A>: Ir. Soekarno');
        $section->addText('A>: Jokowi');
        $section->addText('A>: Megawati');
        $section->addText('K>: C');
        $section->addText('PT>: 2');
        $section->addText('PF>: 0');
        $section->addTextBreak(1);

        $section->addText('--- CONTOH 2: PG KOMPLEKS (Banyak Jawaban) ---', ['bold' => true, 'color' => '0000FF']);
        $section->addText('Q>: Manakah yang termasuk buah-buahan?');
        $section->addText('A>: Ayam');
        $section->addText('A>: Apel');
        $section->addText('A>: Kucing');
        $section->addText('A>: Mangga');
        $section->addText('A>: Besi');
        $section->addText('K>: B, D');
        $section->addText('PT>: 4');
        $section->addTextBreak(1);

        $section->addText('--- CONTOH 3: MENJODOHKAN ---', ['bold' => true, 'color' => '0000FF']);
        $section->addText('Q>: Pasangkan Ibukota Provinsi berikut!');
        $section->addText('B>: Jawa Barat'); 
        $section->addText('B>: Jawa Timur');
        $section->addText('A>: Surabaya');
        $section->addText('A>: Bandung');
        $section->addText('K>: A-B, B-A');
        $section->addTextBreak(1);

        $section->addText('--- CONTOH 4: ISIAN SINGKAT ---', ['bold' => true, 'color' => '0000FF']);
        $section->addText('Q>: 1 minggu ada berapa hari?');
        $section->addText('SE>: 7');
        $section->addTextBreak(1);

        $filename = "Template_Soal_Q_Format.docx";
        
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $xmlWriter->save("php://output");
        exit;
    }

    public function importSoalWord(Request $request, $idBank)
    {
        if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
            return redirect()->back()->with('error', 'Library PhpWord belum diinstall. Jalankan: composer require phpoffice/phpword');
        }

        $request->validate([
            'file_word' => 'required|mimes:docx|max:20480'
        ]);

        $file = $request->file('file_word');

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getPathname());
            
            $rawLines = [];
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $content = $this->parseWordElement($element);
                    
                    if (!empty($content)) {
                        $splitLines = explode(PHP_EOL, $content);
                        foreach($splitLines as $l) {
                            $l = preg_replace('/^\xEF\xBB\xBF/', '', $l);
                            $l = html_entity_decode($l);
                            $l = str_replace(["\xc2\xa0", "&nbsp;"], " ", $l);
                            $l = trim($l);
                            if(!empty($l) || strpos($l, '<img') !== false) {
                                $rawLines[] = $l;
                            }
                        }
                    }
                }
            }

            $jumlahSukses = 0;
            $currentSoal = [];

            DB::beginTransaction();
            foreach ($rawLines as $line) {
                $plainText = trim(strip_tags($line));
                
                if (preg_match('/Q\d*\s*>\s*:/i', $plainText)) {
                    if (!empty($currentSoal)) {
                        $this->simpanSoalParsed($idBank, $currentSoal);
                        $jumlahSukses++;
                    }

                    $cleanContent = preg_replace('/^.*?Q\d*\s*>\s*:\s*/i', '', $line);
                    $currentSoal = [
                        'pertanyaan' => $cleanContent, 
                        'opsi_A' => [], 'opsi_B' => [], 'kunci' => '', 'tipe' => 'PG', 'bobot' => 1, 'bobot_salah' => 0, 'isian' => ''
                    ];
                } 
                elseif (preg_match('/^A\s*>\s*:/i', $plainText)) {
                    $currentSoal['opsi_A'][] = preg_replace('/^.*?A\s*>\s*:\s*/i', '', $line);
                }
                elseif (preg_match('/^B\s*>\s*:/i', $plainText)) {
                    $currentSoal['opsi_B'][] = preg_replace('/^.*?B\s*>\s*:\s*/i', '', $line);
                }
                elseif (preg_match('/^K\s*>\s*:\s*(.*)/i', $plainText, $matches)) {
                    $currentSoal['kunci'] = trim($matches[1]);
                }
                elseif (preg_match('/^PT\s*>\s*:\s*(.*)/i', $plainText, $matches)) {
                    $currentSoal['bobot'] = floatval($matches[1]);
                }
                elseif (preg_match('/^PF\s*>\s*:\s*(.*)/i', $plainText, $matches)) {
                    $currentSoal['bobot_salah'] = floatval($matches[1]);
                }
                elseif (preg_match('/^SE\s*>\s*:\s*(.*)/i', $plainText, $matches)) {
                    $currentSoal['isian'] = trim($matches[1]);
                    $currentSoal['tipe'] = 'ISIAN_SINGKAT';
                }
                elseif (!empty($currentSoal)) {
                    $currentSoal['pertanyaan'] .= '<br>' . $line;
                }
            }

            if (!empty($currentSoal)) {
                $this->simpanSoalParsed($idBank, $currentSoal);
                $jumlahSukses++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Import Selesai! $jumlahSukses soal berhasil masuk.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    private function parseWordElement($element)
    {
        $html = '';
        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $child) {
                $html .= $this->parseWordElement($child);
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Image) {
            $html .= $this->saveImageFromWord($element);
        } elseif (method_exists($element, 'getText')) {
            $html .= $element->getText();
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
            $html .= PHP_EOL; 
        }
        return $html;
    }

    private function saveImageFromWord($imageElement)
    {
        try {
            $path = public_path('uploads/bank_soal/');
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $imageData = $imageElement->getImageStringData(true);
            $ext = strtolower($imageElement->getImageExtension() ?: 'jpg');

            // Coba konversi WMF/EMF ke PNG menggunakan Imagick jika Imagick tersedia
            if (in_array($ext, ['wmf', 'emf'])) {
                if (class_exists('\Imagick')) {
                    try {
                        $imagick = new \Imagick();
                        $imagick->readImageBlob($imageData);
                        $imagick->setImageFormat('png');
                        $imageData = $imagick->getImageBlob();
                        $ext = 'png';
                    } catch (\Exception $ex) {
                        // Jika gagal konversi, biarkan ekstensi aslinya
                    }
                }
            }

            $fileName = 'img_import_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $ext;
            file_put_contents($path . $fileName, $imageData);

            // Gunakan path relatif (diawali /) agar tidak terpengaruh domain/port localhost yang berubah
            return '<img src="/uploads/bank_soal/' . $fileName . '" style="max-width: 100%; height: auto; margin: 10px 0;">';
        } catch (\Exception $e) {
            return '[Gagal load gambar]';
        }
    }

    private function simpanSoalParsed($idBank, $data)
    {
        $tipe = 'PG';
        if (!empty($data['opsi_B'])) {
            $tipe = 'MENJODOHKAN';
        } elseif ($data['tipe'] === 'ISIAN_SINGKAT') {
            $tipe = 'ISIAN_SINGKAT';
        } elseif (strpos($data['kunci'], ',') !== false || strpos($data['kunci'], '-') !== false) {
            $tipe = 'PG_KOMPLEKS'; 
        }

        // Map ke integer
        $jenis_soal = 1;
        if ($tipe == 'PG_KOMPLEKS') $jenis_soal = 3;
        if ($tipe == 'MENJODOHKAN') $jenis_soal = 4;
        if ($tipe == 'ISIAN_SINGKAT') $jenis_soal = 5;

        $soal = \App\Models\SoalData::create([
            'bank_id'      => $idBank,
            'jenis_soal'   => $jenis_soal,
            'question'     => $data['pertanyaan'],
            'true_default_point'  => $data['bobot'] ?? 1,
            'false_default_point' => $data['bobot_salah'] ?? 0
        ]);
        $idSoal = $soal->id;

        $abjad = range('A', 'Z');

        if ($tipe == 'PG' || $tipe == 'PG_KOMPLEKS') {
            $kunciArr = [];
            $rawKunci = explode(',', $data['kunci']);
            foreach($rawKunci as $k) {
                $k = trim($k);
                if(strpos($k, '-') !== false) {
                    $parts = explode('-', $k);
                    if(isset($parts[1]) && trim($parts[1]) == '1') {
                        $kunciArr[] = trim($parts[0]);
                    }
                } else {
                    $kunciArr[] = $k;
                }
            }

            foreach ($data['opsi_A'] as $idx => $teks) {
                $kode = $abjad[$idx] ?? '?';
                $isBenar = in_array($kode, $kunciArr) ? 1 : 0;
                
                \App\Models\SoalOpsi::create([
                    'soal_id'   => $idSoal,
                    'body'      => $teks,
                    'is_key'    => $isBenar
                ]);
            }
        }
        elseif ($tipe == 'MENJODOHKAN') {
            $pairs = explode(',', $data['kunci']); 
            
            // Loop Premis (Opsi B dalam array) sebagai couple
            foreach ($data['opsi_B'] as $idx => $teksKiri) {
                $kodePremis = $abjad[$idx] ?? '?'; 
                $teksKanan = '';

                // Simpan Couple
                $couple = \App\Models\SoalDataCouple::create([
                    'soal_id' => $idSoal,
                    'body' => $teksKiri
                ]);

                foreach($pairs as $p) {
                    $pair = explode('-', trim($p)); 
                    if(trim($pair[0]) == $kodePremis) {
                        $hurufTarget = trim($pair[1] ?? '');
                        $idxTarget = array_search($hurufTarget, $abjad);
                        if($idxTarget !== false && isset($data['opsi_A'][$idxTarget])) {
                            $teksKanan = $data['opsi_A'][$idxTarget];
                        }
                        break;
                    }
                }

                if(!empty($teksKanan)) {
                    \App\Models\SoalOpsi::create([
                        'soal_id' => $idSoal, 
                        'soal_couple_id' => $couple->id,
                        'body' => $teksKanan, 
                        'is_key' => 1
                    ]);
                }
            }
        }
        elseif ($tipe == 'ISIAN_SINGKAT') {
             \App\Models\SoalData::where('id', $idSoal)->update([
                 'shortentry' => $data['isian']
             ]);
        }
    }    public function exportSoal($bank_id)
    {
        $bank = \App\Models\BankSoal::findOrFail($bank_id);
        
        $soalData = \App\Models\SoalData::where('bank_id', $bank_id)->get();
        $soalOpsi = \App\Models\SoalOpsi::whereIn('soal_id', $soalData->pluck('id'))->get()->groupBy('soal_id');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        
        // SET HEADER (Sama persis dengan Template Import)
        $worksheet->setCellValue('A1', 'Pertanyaan');
        $worksheet->setCellValue('B1', 'Tipe (PG/ESSAY)');
        $worksheet->setCellValue('C1', 'Opsi A');
        $worksheet->setCellValue('D1', 'Opsi B');
        $worksheet->setCellValue('E1', 'Opsi C');
        $worksheet->setCellValue('F1', 'Opsi D');
        $worksheet->setCellValue('G1', 'Opsi E');
        $worksheet->setCellValue('H1', 'Kunci (A/B/C/D/E)');
        
        $worksheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($soalData as $soal) {
            // Hanya export soal PG (1) dan ESSAY / Isian Singkat (4/5)
            // Tipe lain (PG Kompleks, Menjodohkan) tidak sepenuhnya disupport oleh format excel bawaan
            if (!in_array($soal->jenis_soal, [1, 4, 5])) continue;

            $tipe = ($soal->jenis_soal == 1) ? 'PG' : 'ESSAY';
            
            // Bersihkan tag HTML
            $pertanyaan = strip_tags(str_replace(['<br>', '</p>'], "\n", $soal->question));

            $worksheet->setCellValue("A{$row}", $pertanyaan);
            $worksheet->setCellValue("B{$row}", $tipe);

            if ($soal->jenis_soal == 1) {
                // Pilihan Ganda
                $opsiList = isset($soalOpsi[$soal->id]) ? $soalOpsi[$soal->id] : collect();
                $cols = ['C', 'D', 'E', 'F', 'G'];
                $kunciStr = '';
                
                foreach ($opsiList as $idx => $opsi) {
                    if ($idx >= 5) break; // Maksimal 5 opsi
                    
                    $teksOpsi = strip_tags(str_replace(['<br>', '</p>'], "\n", $opsi->body));
                    $worksheet->setCellValue("{$cols[$idx]}{$row}", $teksOpsi);
                    
                    if ($opsi->is_key) {
                        $kunciStr = chr(65 + $idx); // 0=A, 1=B, dst.
                    }
                }
                
                $worksheet->setCellValue("H{$row}", $kunciStr);
            } else {
                // Essay / Isian Singkat
                // Tidak ada opsi, kunci kalau isian singkat bisa ditaruh di H
                if ($soal->jenis_soal == 5) {
                    $worksheet->setCellValue("H{$row}", $soal->shortentry);
                }
            }

            $row++;
        }
        
        foreach (range('A', 'H') as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "Export_Soal_{$bank->kode}.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } 

    private function moveResource($str, $path)
    {
        // Cari gambar dan video untuk dimasukkan ke ZIP Excel
        $pattern = '/<source[^>]+src="([^">]+)/i';
        if (preg_match_all($pattern, $str, $matches)) {
            foreach ($matches[1] as $sk => $sv) {
                // Konversi URL aplikasi menjadi absolute path di public_path
                $file = str_replace([url('/'), "{BASEURL}"], [public_path(), public_path()], $sv);
                if (file_exists($file)) @copy($file, "$path/" . basename($file));
            }
        }
        
        $pattern = '/<img[^>]+src="([^">]+)/i';
        if (preg_match_all($pattern, $str, $matches)) {
            foreach ($matches[1] as $sk => $sv) {
                // Cek local path jika URL
                $file = str_replace([url('/'), "{BASEURL}"], [public_path(), public_path()], $sv);
                if (file_exists($file)) @copy($file, "$path/" . basename($file));
            }
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke folder public/uploads/tinymce
            $destinationPath = public_path('uploads/tinymce');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);
            
            // Return JSON format yang diharapkan oleh TinyMCE
            return response()->json([
                'location' => asset('uploads/tinymce/' . $filename)
            ]);
        }

        return response()->json(['error' => 'Gagal mengupload gambar.'], 400);
    }
}
