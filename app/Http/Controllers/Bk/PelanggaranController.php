<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiswaPelanggaran;
use App\Models\MasterPelanggaran;
use App\Models\Siswa;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = SiswaPelanggaran::with(['siswa.kelas', 'pelanggaran', 'pelapor']);

        // Filter Rentang Tanggal
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Pencarian Nama atau Jenis Pelanggaran
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            })->orWhereHas('pelanggaran', function($q) use ($search) {
                $q->where('nama_pelanggaran', 'like', "%{$search}%");
            });
        }

        // 1. Ambil Riwayat Pelanggaran (Paginasi)
        $riwayat = $query->orderBy('tanggal', 'DESC')->paginate(10)->withQueryString();

        $set_sp = \App\Models\SetSp::first();
        if (!$set_sp) {
            $set_sp = \App\Models\SetSp::create(['sp_1' => 50, 'sp_2' => 30, 'sp_3' => 0]);
        }

        // 2. Data Statistik Siswa Paling Kritis
        $total_kasus = $riwayat->count();
        $poin_kritis = DB::table('tbl_siswa_pelanggaran as p')
            ->join('tbl_siswa as s', 's.id', '=', 'p.siswa_id')
            ->join('tbl_master_pelanggaran as m', 'm.id', '=', 'p.pelanggaran_id')
            ->select('s.nama_lengkap', DB::raw('100 - SUM(m.poin) as sisa_poin'))
            ->groupBy('p.siswa_id', 's.nama_lengkap')
            ->orderBy('sisa_poin', 'ASC')
            ->limit(1)
            ->first();

        // 3. Data Pendukung untuk Modal
        $siswa = Siswa::with('kelas')->orderBy('nama_lengkap', 'ASC')->get();
        $jenis = MasterPelanggaran::all();

        return Inertia::render('Bk/Pelanggaran/Index', [
            'riwayat' => $riwayat,
            'siswa' => $siswa,
            'jenis' => $jenis,
            'set_sp' => $set_sp,
            'stats' => [
                'total' => $total_kasus,
                'top_siswa' => $poin_kritis ? $poin_kritis->nama_lengkap . ' (Sisa ' . $poin_kritis->sisa_poin . ' Poin)' : '-'
            ],
            'filters' => $request->only(['search', 'start_date', 'end_date'])
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = SiswaPelanggaran::with(['siswa.kelas', 'pelanggaran', 'pelapor']);

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            })->orWhereHas('pelanggaran', function($q) use ($search) {
                $q->where('nama_pelanggaran', 'like', "%{$search}%");
            });
        }

        $riwayat = $query->orderBy('tanggal', 'DESC')->get();
        $sekolah = \App\Models\Sekolah::first();
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'search' => $request->search
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bk.pelanggaran.pdf', compact('riwayat', 'sekolah', 'filters'))
               ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Buku_Kasus_Siswa.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:tbl_siswa,id',
            'pelanggaran_id' => 'required|exists:tbl_master_pelanggaran,id'
        ]);

        SiswaPelanggaran::create([
            'siswa_id'       => $request->siswa_id,
            'pelanggaran_id' => $request->pelanggaran_id,
            'pelapor_id'     => Auth::id() ?? 1,
            'tanggal'        => date('Y-m-d H:i:s'),
            'catatan'        => $request->catatan,
            'status'         => 'Baru'
        ]);

        return back()->with('message', 'Pelanggaran berhasil dicatat.');
    }

    public function destroy($id)
    {
        $pelanggaran = SiswaPelanggaran::findOrFail($id);
        $pelanggaran->delete();
        return back()->with('message', 'Data pelanggaran berhasil dihapus.');
    }

    public function updateSp(Request $request)
    {
        $request->validate([
            'sp_1' => 'required|numeric',
            'sp_2' => 'required|numeric',
            'sp_3' => 'required|numeric',
        ]);

        $set_sp = \App\Models\SetSp::first();
        if ($set_sp) {
            $set_sp->update([
                'sp_1' => $request->sp_1,
                'sp_2' => $request->sp_2,
                'sp_3' => $request->sp_3
            ]);
        } else {
            \App\Models\SetSp::create([
                'sp_1' => $request->sp_1,
                'sp_2' => $request->sp_2,
                'sp_3' => $request->sp_3
            ]);
        }

        return back()->with('message', 'Pengaturan Batas SP berhasil diperbarui.');
    }

    public function rekapSiswa(Request $request)
    {
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'ASC')->get();
        
        $set_sp = \App\Models\SetSp::first();
        if (!$set_sp) {
            $set_sp = \App\Models\SetSp::create(['sp_1' => 50, 'sp_2' => 30, 'sp_3' => 0]);
        }

        $siswa = [];
        $selected_kelas = $request->kelas_id;

        if ($selected_kelas) {
            $siswa = \App\Models\Siswa::with('kelas')
                ->where('kelas_id', $selected_kelas)
                ->orderBy('nama_lengkap', 'ASC')
                ->get()
                ->map(function ($s) {
                    $total_poin = DB::table('tbl_siswa_pelanggaran as p')
                        ->join('tbl_master_pelanggaran as m', 'm.id', '=', 'p.pelanggaran_id')
                        ->where('p.siswa_id', $s->id)
                        ->sum('m.poin');
                    
                    $sisa_poin = 100 - $total_poin;
                    $s->sisa_poin = $sisa_poin;
                    return $s;
                });
        }

        return Inertia::render('Bk/Pelanggaran/RekapSiswa', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'set_sp' => $set_sp,
            'selected_kelas' => $selected_kelas
        ]);
    }
}
