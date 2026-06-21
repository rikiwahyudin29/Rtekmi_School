<?php

namespace App\Http\Controllers\Admin\Sarpras;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use Inertia\Inertia;

class PeminjamanRuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = PeminjamanRuangan::with('ruangan');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('peminjam', 'like', "%{$search}%")
                  ->orWhere('kegiatan', 'like', "%{$search}%")
                  ->orWhereHas('ruangan', function($qr) use ($search) {
                      $qr->where('nama_ruangan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status != 'Semua') {
            $query->where('status', $request->status);
        }

        $peminjaman = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $ruanganList = Ruangan::orderBy('nama_ruangan', 'asc')->get();

        return Inertia::render('Admin/Sarpras/Peminjaman/Index', [
            'peminjaman' => $peminjaman,
            'ruanganList' => $ruanganList,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required|exists:tbl_ruangan,id',
            'peminjam' => 'required|string|max:150',
            'kegiatan' => 'required|string|max:200',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali' => 'required|date|after_or_equal:tgl_pinjam',
            'catatan' => 'nullable|string'
        ]);

        PeminjamanRuangan::create([
            ...$request->all(),
            'status' => 'Menunggu'
        ]);

        return back()->with('message', 'Pengajuan peminjaman ruangan berhasil dicatat.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu,Disetujui,Ditolak,Selesai'
        ]);

        $item = PeminjamanRuangan::findOrFail($id);
        $item->update(['status' => $request->status]);

        return back()->with('message', 'Status peminjaman berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = PeminjamanRuangan::findOrFail($id);
        $item->delete();

        return back()->with('message', 'Data peminjaman berhasil dihapus.');
    }
}
