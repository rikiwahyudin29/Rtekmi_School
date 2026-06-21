<?php

namespace App\Http\Controllers\Admin\Humas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BukuTamu;
use App\Models\Sekolah;
use Carbon\Carbon;

class BukuTamuController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->input('tgl_awal', Carbon::now()->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', Carbon::now()->format('Y-m-d'));
        $kategori = $request->input('kategori', 'Semua');
        $search = $request->input('search');

        $query = BukuTamu::whereBetween('tanggal', [$tgl_awal, $tgl_akhir]);

        if ($kategori !== 'Semua') {
            $query->where('kategori', $kategori);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('instansi_asal', 'like', "%{$search}%")
                  ->orWhere('keperluan', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $statsQuery = clone $query;
        $totalTamu = $statsQuery->count();
        $statsMenunggu = (clone $query)->where('status', 'Menunggu')->count();
        $statsSelesai = (clone $query)->where('status', 'Selesai')->count();

        $tamu = $query->orderBy('tanggal', 'desc')->orderBy('no_antrian', 'asc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Humas/BukuTamu/Index', [
            'tamu' => $tamu,
            'stats' => [
                'total' => $totalTamu,
                'menunggu' => $statsMenunggu,
                'selesai' => $statsSelesai,
            ],
            'filters' => [
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
                'kategori' => $kategori,
                'search' => $search
            ]
        ]);
    }

    public function destroy($id)
    {
        BukuTamu::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Data tamu berhasil dihapus!');
    }

    public function cetakRekap(Request $request)
    {
        $tgl_awal = $request->input('tgl_awal', Carbon::now()->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', Carbon::now()->format('Y-m-d'));
        $kategori = $request->input('kategori', 'Semua');

        $query = BukuTamu::whereBetween('tanggal', [$tgl_awal, $tgl_akhir]);

        if ($kategori !== 'Semua') {
            $query->where('kategori', $kategori);
        }

        $tamu = $query->orderBy('tanggal', 'asc')->orderBy('no_antrian', 'asc')->get();
        $sekolah = Sekolah::find(1);

        return view('cetak.rekap_buku_tamu', compact('tamu', 'tgl_awal', 'tgl_akhir', 'kategori', 'sekolah'));
    }
}
