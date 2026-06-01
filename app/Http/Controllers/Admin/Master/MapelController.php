<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $kelompokFilter = $request->input('kelompok');

        $mapel = Mapel::when($search, function ($query, $search) {
                $query->where('nama_mapel', 'like', "%{$search}%")
                      ->orWhere('kode_mapel', 'like', "%{$search}%");
            })
            ->when($kelompokFilter, function ($query, $kelompokFilter) {
                $query->where('kelompok', $kelompokFilter);
            })
            ->orderBy('kelompok', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $jurusans = Jurusan::select('id', 'nama_jurusan', 'kode_jurusan')->orderBy('nama_jurusan', 'asc')->get();

        return Inertia::render('Admin/Master/Mapel/Index', [
            'mapel' => $mapel,
            'jurusans' => $jurusans,
            'filters' => $request->only(['search', 'per_page', 'kelompok']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok' => 'nullable|string|max:2',
            'jurusan_id' => 'nullable|string', // Could be comma separated like "1,2" or "0"
            'tampil_raport' => 'nullable|boolean',
            'tampil_skl' => 'nullable|boolean',
            'tampil_transkrip' => 'nullable|boolean',
        ]);

        // Default value checks
        $validated['tampil_raport'] = $validated['tampil_raport'] ?? true;
        $validated['tampil_skl'] = $validated['tampil_skl'] ?? true;
        $validated['tampil_transkrip'] = $validated['tampil_transkrip'] ?? true;
        $validated['jurusan_id'] = $validated['jurusan_id'] ?? '0';

        Mapel::create($validated);

        return back()->with('message', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok' => 'nullable|string|max:2',
            'jurusan_id' => 'nullable|string',
            'tampil_raport' => 'nullable|boolean',
            'tampil_skl' => 'nullable|boolean',
            'tampil_transkrip' => 'nullable|boolean',
        ]);

        $mapel->update($validated);

        return back()->with('message', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Mapel::findOrFail($id)->delete();
            return back()->with('message', 'Mata Pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'KODE MAPEL');
        $sheet->setCellValue('B1', 'NAMA MAPEL');
        $sheet->setCellValue('C1', 'KELOMPOK (Contoh: A/B/C)');

        // Style header
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFCCCCCC');
              
        // Auto size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Dummy data
        $sheet->setCellValue('A2', 'MAT-01');
        $sheet->setCellValue('B2', 'Matematika Dasar');
        $sheet->setCellValue('C2', 'A');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Mata_Pelajaran.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $inserted = 0;
            for ($row = 2; $row <= $highestRow; $row++) {
                $kode_mapel = $sheet->getCell('A' . $row)->getValue();
                $nama_mapel = $sheet->getCell('B' . $row)->getValue();
                $kelompok = $sheet->getCell('C' . $row)->getValue();

                if ($nama_mapel) {
                    Mapel::updateOrCreate(
                        ['kode_mapel' => $kode_mapel],
                        [
                            'nama_mapel' => $nama_mapel,
                            'kelompok' => $kelompok,
                            'jurusan_id' => '0',
                            'tampil_raport' => true,
                            'tampil_skl' => true,
                            'tampil_transkrip' => true,
                        ]
                    );
                    $inserted++;
                }
            }

            return back()->with('message', "Berhasil mengimport {$inserted} mata pelajaran.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
