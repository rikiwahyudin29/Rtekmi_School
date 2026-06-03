<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\JenisBayar;
use App\Models\Tagihan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $jenis_filter = $request->get('filter', 'harian');
        $tanggal_awal = $request->get('start');
        $tanggal_akhir = $request->get('end');

        if (empty($tanggal_awal)) {
            if ($jenis_filter == 'harian') {
                $tanggal_awal = date('Y-m-d');
                $tanggal_akhir = date('Y-m-d');
            } elseif ($jenis_filter == 'mingguan') {
                $tanggal_awal = date('Y-m-d', strtotime('monday this week'));
                $tanggal_akhir = date('Y-m-d', strtotime('sunday this week'));
            } elseif ($jenis_filter == 'bulanan') {
                $tanggal_awal = date('Y-m-01');
                $tanggal_akhir = date('Y-m-t');
            } elseif ($jenis_filter == 'tahunan') {
                $tanggal_awal = date('Y-01-01');
                $tanggal_akhir = date('Y-12-31');
            }
        }

        $transaksi = Transaksi::with(['siswa.kelas', 'tagihan.jenisBayar.posBayar', 'tagihan.jenisBayar.tahunAjaran'])
            ->whereDate('tanggal_bayar', '>=', $tanggal_awal)
            ->whereDate('tanggal_bayar', '<=', $tanggal_akhir)
            ->orderBy('created_at', 'desc')
            ->get();

        $pengeluaran = Pengeluaran::with(['divisi', 'jenis'])
            ->whereDate('tanggal', '>=', $tanggal_awal)
            ->whereDate('tanggal', '<=', $tanggal_akhir)
            ->orderBy('tanggal', 'desc')
            ->get();

        $total_masuk = $transaksi->sum('jumlah_bayar');
        $total_keluar = $pengeluaran->sum('nominal');
        $saldo_akhir = $total_masuk - $total_keluar;

        // Rekapitulasi per Jenis Bayar
        $rekap = JenisBayar::with(['posBayar', 'tahunAjaran'])->get()->map(function ($j) {
            $tagihan = Tagihan::where('id_jenis_bayar', $j->id)->get();
            
            $j->total_siswa_ditagih = $tagihan->count();
            $j->total_potensi_rupiah = $tagihan->sum('nominal_tagihan');
            $j->uang_masuk = $tagihan->sum('nominal_terbayar');
            $j->uang_tunggakan = $j->total_potensi_rupiah - $j->uang_masuk;
            
            $j->siswa_lunas = $tagihan->where('status_bayar', 'LUNAS')->count();
            $j->siswa_belum_lunas = $tagihan->where('status_bayar', '!=', 'LUNAS')->count();
            
            return $j;
        });

        return Inertia::render('Admin/Keuangan/Laporan/Index', [
            'filter'      => $jenis_filter,
            'start'       => $tanggal_awal,
            'end'         => $tanggal_akhir,
            'transaksi'   => $transaksi,
            'pengeluaran' => $pengeluaran,
            'total_masuk' => $total_masuk,
            'total_keluar'=> $total_keluar,
            'saldo_akhir' => $saldo_akhir,
            'rekap'       => $rekap
        ]);
    }

    public function cetakTransaksi(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $transaksi = Transaksi::with(['siswa.kelas', 'tagihan.jenisBayar.posBayar', 'tagihan.jenisBayar.tahunAjaran'])
            ->whereDate('tanggal_bayar', '>=', $start)
            ->whereDate('tanggal_bayar', '<=', $end)
            ->orderBy('created_at', 'asc')
            ->get();

        $pengeluaran = Pengeluaran::with(['divisi', 'jenis'])
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('keuangan.laporan.cetak_transaksi', [
            'transaksi'   => $transaksi,
            'pengeluaran' => $pengeluaran,
            'start'       => $start,
            'end'         => $end,
            'sekolah'     => \App\Models\Sekolah::first()
        ]);
    }

    public function cetakTunggakan(Request $request)
    {
        $rekap = JenisBayar::with(['posBayar', 'tahunAjaran'])->get()->map(function ($j) {
            $tagihan = Tagihan::where('id_jenis_bayar', $j->id)->get();
            
            $j->total_siswa = $tagihan->count();
            $j->total_tagihan = $tagihan->sum('nominal_tagihan');
            $j->total_bayar = $tagihan->sum('nominal_terbayar');
            $j->total_tunggakan = $j->total_tagihan - $j->total_bayar;
            
            $j->qty_lunas = $tagihan->where('status_bayar', 'LUNAS')->count();
            $j->qty_belum = $tagihan->where('status_bayar', '!=', 'LUNAS')->count();
            
            return $j;
        });

        return view('keuangan.laporan.cetak_tunggakan', [
            'rekap'   => $rekap,
            'sekolah' => \App\Models\Sekolah::first()
        ]);
    }

    public function exportExcel(Request $request)
    {
        $start = $request->get('start') ?? date('Y-m-01');
        $end   = $request->get('end')   ?? date('Y-m-d');

        $transaksi = Transaksi::with(['siswa.kelas', 'tagihan.jenisBayar.posBayar'])
            ->whereDate('tanggal_bayar', '>=', $start)
            ->whereDate('tanggal_bayar', '<=', $end)
            ->orderBy('created_at', 'asc')
            ->get();

        $pengeluaran = Pengeluaran::with(['divisi', 'jenis'])
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->orderBy('tanggal', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- HEADER LAPORAN ---
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN SEKOLAH');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start)) . ' s/d ' . date('d/m/Y', strtotime($end)));
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // --- BAGIAN 1: PEMASUKAN ---
        $row = 4;
        $sheet->setCellValue('A' . $row, 'A. PEMASUKAN (PEMBAYARAN SISWA)');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        // Header Tabel Masuk
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Tanggal');
        $sheet->setCellValue('C' . $row, 'Siswa');
        $sheet->setCellValue('D' . $row, 'Keterangan');
        $sheet->setCellValue('E' . $row, 'Nominal (Rp)');
        
        // Style Header
        $sheet->getStyle("A$row:E$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $row++;
        $no = 1;
        $totalMasuk = 0;
        foreach ($transaksi as $t) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($t->created_at)));
            $sheet->setCellValue('C' . $row, $t->siswa->nama_lengkap ?? '-');
            
            $ket_bayar = $t->tagihan->jenisBayar->posBayar->nama_pos ?? 'Pembayaran';
            $ket_tambahan = $t->tagihan->keterangan ? ' - ' . $t->tagihan->keterangan : '';
            if (!$t->tagihan->keterangan && $t->tagihan->bulan_ke > 0) {
                $ket_tambahan = ' (Bulan ' . $t->tagihan->bulan_ke . ')';
            }

            $sheet->setCellValue('D' . $row, $ket_bayar . $ket_tambahan);
            $sheet->setCellValue('E' . $row, $t->jumlah_bayar);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $totalMasuk += $t->jumlah_bayar;
            $row++;
        }
        // Total Masuk
        $sheet->setCellValue('D' . $row, 'TOTAL PEMASUKAN');
        $sheet->setCellValue('E' . $row, $totalMasuk);
        $sheet->getStyle("D$row:E$row")->getFont()->setBold(true);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $row += 3; 

        // --- BAGIAN 2: PENGELUARAN ---
        $sheet->setCellValue('A' . $row, 'B. PENGELUARAN OPERASIONAL');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        // Header Tabel Keluar
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Tanggal');
        $sheet->setCellValue('C' . $row, 'Judul & Divisi');
        $sheet->setCellValue('D' . $row, 'Keterangan');
        $sheet->setCellValue('E' . $row, 'Nominal (Rp)');
        
        $sheet->getStyle("A$row:E$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row++;
        $no = 1;
        $totalKeluar = 0;
        foreach ($pengeluaran as $p) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($p->tanggal)));
            $divisi_name = $p->divisi ? $p->divisi->nama_divisi : '-';
            $sheet->setCellValue('C' . $row, $p->judul_pengeluaran . " (" . $divisi_name . ")");
            $sheet->setCellValue('D' . $row, $p->keterangan);
            $sheet->setCellValue('E' . $row, $p->nominal);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $totalKeluar += $p->nominal;
            $row++;
        }
        // Total Keluar
        $sheet->setCellValue('D' . $row, 'TOTAL PENGELUARAN');
        $sheet->setCellValue('E' . $row, $totalKeluar);
        $sheet->getStyle("D$row:E$row")->getFont()->setBold(true);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $row += 2;

        // --- BAGIAN 3: SALDO AKHIR ---
        $sheet->setCellValue('D' . $row, 'SURPLUS / DEFISIT');
        $sheet->setCellValue('E' . $row, $totalMasuk - $totalKeluar);
        $sheet->getStyle("D$row:E$row")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Auto Width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Export
        $filename = "Laporan_Keuangan_" . date('Ymd', strtotime($start)) . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
