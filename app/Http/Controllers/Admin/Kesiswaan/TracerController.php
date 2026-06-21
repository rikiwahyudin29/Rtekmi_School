<?php

namespace App\Http\Controllers\Admin\Kesiswaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TracerController extends Controller
{
    public function index()
    {
        // 1. Data Pertanyaan Kuesioner
        $pertanyaan = DB::table('tbl_tracer_pertanyaan')->get();

        // 2. Data Laporan Alumni (Responden)
        $responden = DB::table('tbl_tracer_responden')
            ->select('tbl_tracer_responden.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_siswa.tahun_angkatan')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_tracer_responden.siswa_id')
            ->orderBy('tbl_tracer_responden.tanggal_isi', 'DESC')
            ->get();

        // 3. Hitung Statistik Dasar untuk Chart (Jumlah per status_kegiatan)
        $stats = DB::table('tbl_tracer_responden')
            ->select('status_kegiatan', DB::raw('COUNT(id) as total'))
            ->groupBy('status_kegiatan')
            ->get();

        return Inertia::render('Admin/Kesiswaan/Tracer/Index', [
            'pertanyaan' => $pertanyaan,
            'responden' => $responden,
            'stats' => $stats
        ]);
    }

    public function simpanPertanyaan(Request $request)
    {
        $validated = $request->validate([
            'pertanyaan'   => 'required|string|max:255',
            'tipe'         => 'required|in:text,textarea,radio,checkbox',
            'opsi_jawaban' => 'nullable|string',
            'is_required'  => 'nullable|boolean'
        ]);

        $validated['is_required'] = $request->has('is_required') ? $request->input('is_required') : 1;

        DB::table('tbl_tracer_pertanyaan')->insert($validated);

        return back()->with('message', 'Pertanyaan baru berhasil ditambahkan ke kuesioner!');
    }

    public function hapusPertanyaan($id)
    {
        DB::table('tbl_tracer_pertanyaan')->where('id', $id)->delete();
        DB::table('tbl_tracer_jawaban')->where('pertanyaan_id', $id)->delete();

        return back()->with('message', 'Pertanyaan berhasil dihapus dari sistem.');
    }

    public function detail($id)
    {
        $responden = DB::table('tbl_tracer_responden')
            ->select('tbl_tracer_responden.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_siswa.tahun_angkatan', 'tbl_siswa.no_hp_siswa')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_tracer_responden.siswa_id')
            ->where('tbl_tracer_responden.id', $id)
            ->first();

        if (!$responden) {
            return redirect()->route('admin.kesiswaan.tracer.index')->with('error', 'Data responden tidak ditemukan.');
        }

        // Tarik jawaban beserta isi pertanyaannya
        $jawaban = DB::table('tbl_tracer_jawaban')
            ->select('tbl_tracer_jawaban.*', 'tbl_tracer_pertanyaan.pertanyaan')
            ->join('tbl_tracer_pertanyaan', 'tbl_tracer_pertanyaan.id', '=', 'tbl_tracer_jawaban.pertanyaan_id')
            ->where('tbl_tracer_jawaban.responden_id', $id)
            ->get();

        return Inertia::render('Admin/Kesiswaan/Tracer/Detail', [
            'responden' => $responden,
            'jawaban' => $jawaban
        ]);
    }

    public function resetResponden($id)
    {
        DB::beginTransaction();
        try {
            DB::table('tbl_tracer_jawaban')->where('responden_id', $id)->delete();
            DB::table('tbl_tracer_responden')->where('id', $id)->delete();
            DB::commit();
            return back()->with('message', 'Data responden berhasil di-reset. Alumni dapat mengisi ulang kuesioner.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mereset responden: ' . $e->getMessage());
        }
    }

    public function exportTracer()
    {
        $responden = DB::table('tbl_tracer_responden')
            ->select('tbl_tracer_responden.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_siswa.tahun_angkatan')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_tracer_responden.siswa_id')
            ->orderBy('tbl_tracer_responden.tanggal_isi', 'DESC')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NAMA LENGKAP');
        $sheet->setCellValue('C1', 'NISN');
        $sheet->setCellValue('D1', 'ANGKATAN');
        $sheet->setCellValue('E1', 'STATUS SAAT INI');
        $sheet->setCellValue('F1', 'NAMA INSTANSI / KAMPUS');
        $sheet->setCellValue('G1', 'TANGGAL ISI');

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']], // Emerald 500
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($styleArray);

        $rowNum = 2;
        $no = 1;
        foreach ($responden as $r) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $r->nama_lengkap);
            $sheet->setCellValueExplicit('C' . $rowNum, $r->nisn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNum, $r->tahun_angkatan ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $r->status_kegiatan);
            $sheet->setCellValue('F' . $rowNum, $r->nama_instansi);
            $sheet->setCellValue('G' . $rowNum, date('d/m/Y', strtotime($r->tanggal_isi)));
            $rowNum++;
        }

        foreach(range('A','G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Laporan_Tracer_Study_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
