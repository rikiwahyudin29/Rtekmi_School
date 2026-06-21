<?php

namespace App\Http\Controllers\Admin\Kesiswaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RombelController extends Controller
{
    // ==========================================
    // MANAJEMEN ROMBEL & NAIK KELAS
    // ==========================================
    
    public function index()
    {
        // Ambil semua kelas dan hitung jumlah siswa aktif di tiap kelas
        $kelas = DB::table('tbl_kelas')
            ->select('tbl_kelas.*', DB::raw('(SELECT COUNT(*) FROM tbl_siswa WHERE tbl_siswa.kelas_id = tbl_kelas.id AND tbl_siswa.status_siswa = "Aktif") as total_siswa'))
            ->orderBy('nama_kelas', 'ASC')
            ->get();

        return Inertia::render('Admin/Kesiswaan/Rombel/Index', [
            'kelas' => $kelas
        ]);
    }

    public function atur($id_kelas)
    {
        $kelasAsal = DB::table('tbl_kelas')->where('id', $id_kelas)->first();
        
        if (!$kelasAsal) {
            return redirect()->route('admin.kesiswaan.rombel.index')->with('error', 'Data kelas tidak ditemukan.');
        }

        // Ambil siswa yang AKTIF di kelas ini
        $siswa = DB::table('tbl_siswa')
            ->where('kelas_id', $id_kelas)
            ->where('status_siswa', 'Aktif')
            ->orderBy('nama_lengkap', 'ASC')
            ->get();

        $kelas_all = DB::table('tbl_kelas')->orderBy('nama_kelas', 'ASC')->get();

        return Inertia::render('Admin/Kesiswaan/Rombel/Atur', [
            'kelas_asal' => $kelasAsal,
            'siswa' => $siswa,
            'kelas_all' => $kelas_all
        ]);
    }

    public function prosesPindah(Request $request)
    {
        $id_siswa_array = $request->input('siswa_id', []); // Array ID siswa yang dicentang
        $aksi           = $request->input('aksi');     // 'pindah' atau 'lulus'
        $id_kelas_tujuan= $request->input('kelas_tujuan');

        if (empty($id_siswa_array)) {
            return back()->with('error', 'Pilih minimal satu siswa!');
        }

        DB::beginTransaction();
        try {
            if ($aksi == 'pindah') {
                if (empty($id_kelas_tujuan)) {
                    return back()->with('error', 'Pilih kelas tujuan untuk kenaikan kelas!');
                }

                DB::table('tbl_siswa')
                    ->whereIn('id', $id_siswa_array)
                    ->update(['kelas_id' => $id_kelas_tujuan]);
                
                $msg = count($id_siswa_array) . ' Siswa berhasil dipindahkan/naik kelas!';

            } elseif ($aksi == 'lulus') {
                DB::table('tbl_siswa')
                    ->whereIn('id', $id_siswa_array)
                    ->update([
                        'status_siswa'   => 'Lulus',
                        'kelas_id'       => null,
                        'tahun_angkatan' => date('Y')
                    ]);
                $msg = count($id_siswa_array) . ' Siswa berhasil diluluskan (Angkatan ' . date('Y') . ')!';
            }

            DB::commit();
            return redirect()->route('admin.kesiswaan.rombel.index')->with('message', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    // ==========================================
    // DATA ALUMNI
    // ==========================================

    public function alumni(Request $request)
    {
        $search = $request->input('search');
        $per_page = $request->input('per_page', 10);

        $builder = DB::table('tbl_siswa')
            ->where('status_siswa', 'Lulus')
            ->orderBy('nama_lengkap', 'ASC');

        if (!empty($search)) {
            $builder->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%$search%")
                  ->orWhere('nisn', 'like', "%$search%")
                  ->orWhere('tahun_angkatan', 'like', "%$search%");
            });
        }

        $alumni = $builder->paginate($per_page)->withQueryString();

        return Inertia::render('Admin/Kesiswaan/Alumni/Index', [
            'alumni' => $alumni,
            'search' => $search,
            'per_page' => $per_page
        ]);
    }

    public function simpanAlumni(Request $request)
    {
        $validated = $request->validate([
            'nisn'           => 'required|string|max:20',
            'nama_lengkap'   => 'required|string|max:150',
            'jenis_kelamin'  => 'required|in:L,P',
            'no_hp_siswa'    => 'nullable|string|max:20',
            'alamat'         => 'nullable|string',
            'tahun_angkatan' => 'required|string|max:10'
        ]);

        $validated['status_siswa'] = 'Lulus';
        $validated['kelas_id'] = null; // Karena alumni

        DB::table('tbl_siswa')->insert($validated);

        return back()->with('message', 'Data alumni lampau berhasil ditambahkan ke dalam sistem!');
    }

    public function templateImport()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'NISN');
        $sheet->setCellValue('B1', 'NAMA LENGKAP');
        $sheet->setCellValue('C1', 'L/P');
        $sheet->setCellValue('D1', 'NO HP');
        $sheet->setCellValue('E1', 'ALAMAT');
        $sheet->setCellValue('F1', 'TAHUN ANGKATAN');

        // Style the header
        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($styleArray);

        // Contoh Data
        $sheet->setCellValue('A2', '0081234567');
        $sheet->setCellValue('B2', 'CONTOH NAMA SISWA');
        $sheet->setCellValue('C2', 'L');
        $sheet->setCellValue('D2', '081234567890');
        $sheet->setCellValue('E2', 'JLN. CONTOH ALAMAT NO. 1');
        $sheet->setCellValue('F2', '2023/2024');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Template_Import_Alumni.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function prosesImport(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
        $spreadsheet = $reader->load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Mulai dari index 1 karena index 0 adalah header
        $inserted = 0;
        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row[0]) && empty($row[1])) continue; // Skip jika kosong

                DB::table('tbl_siswa')->insert([
                    'nisn' => $row[0] ?? '-',
                    'nama_lengkap' => strtoupper($row[1] ?? 'TANPA NAMA'),
                    'jenis_kelamin' => strtoupper($row[2] ?? 'L'),
                    'no_hp_siswa' => $row[3] ?? null,
                    'alamat' => $row[4] ?? null,
                    'tahun_angkatan' => $row[5] ?? '-',
                    'status_siswa' => 'Lulus',
                    'kelas_id' => null
                ]);
                $inserted++;
            }
            DB::commit();
            return back()->with('message', $inserted . ' Data alumni berhasil di-import!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    public function exportAlumni(Request $request)
    {
        $search = $request->input('search');
        
        $builder = DB::table('tbl_siswa')
            ->where('status_siswa', 'Lulus')
            ->orderBy('nama_lengkap', 'ASC');

        if (!empty($search)) {
            $builder->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%$search%")
                  ->orWhere('nisn', 'like', "%$search%")
                  ->orWhere('tahun_angkatan', 'like', "%$search%");
            });
        }
        
        $alumni = $builder->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NISN');
        $sheet->setCellValue('C1', 'NAMA LENGKAP');
        $sheet->setCellValue('D1', 'L/P');
        $sheet->setCellValue('E1', 'NO HP');
        $sheet->setCellValue('F1', 'ALAMAT');
        $sheet->setCellValue('G1', 'TAHUN ANGKATAN');

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($styleArray);

        $rowNum = 2;
        $no = 1;
        foreach ($alumni as $a) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValueExplicit('B' . $rowNum, $a->nisn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $a->nama_lengkap);
            $sheet->setCellValue('D' . $rowNum, $a->jenis_kelamin);
            $sheet->setCellValueExplicit('E' . $rowNum, $a->no_hp_siswa, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $rowNum, $a->alamat);
            $sheet->setCellValue('G' . $rowNum, $a->tahun_angkatan);
            $rowNum++;
        }

        foreach(range('A','G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Data_Alumni_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
