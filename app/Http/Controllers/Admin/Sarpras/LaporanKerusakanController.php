<?php

namespace App\Http\Controllers\Admin\Sarpras;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaporanKerusakan;
use App\Models\Inventaris;
use Inertia\Inertia;

class LaporanKerusakanController extends Controller
{
    public function index(Request $request)
    {
        $query = LaporanKerusakan::with('inventaris');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pelapor', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('inventaris', function($qi) use ($search) {
                      $qi->where('nama_barang', 'like', "%{$search}%")
                         ->orWhere('kode_barang', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status != 'Semua') {
            $query->where('status', $request->status);
        }

        $laporan = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $inventarisList = Inventaris::orderBy('nama_barang', 'asc')->get();

        return Inertia::render('Admin/Sarpras/Kerusakan/Index', [
            'laporan' => $laporan,
            'inventarisList' => $inventarisList,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'inventaris_id' => 'required|exists:tbl_inventaris,id',
            'pelapor' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'tgl_lapor' => 'required|date'
        ]);

        LaporanKerusakan::create([
            ...$request->all(),
            'status' => 'Dilaporkan'
        ]);

        // Ubah kondisi inventaris menjadi Rusak
        $inventaris = Inventaris::find($request->inventaris_id);
        if ($inventaris) {
            $inventaris->update(['kondisi' => 'Rusak Ringan']);
        }

        return back()->with('message', 'Laporan kerusakan berhasil dicatat.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Dilaporkan,Proses Perbaikan,Selesai',
            'tindakan_perbaikan' => 'nullable|string'
        ]);

        $item = LaporanKerusakan::findOrFail($id);
        $item->update([
            'status' => $request->status,
            'tindakan_perbaikan' => $request->tindakan_perbaikan ?? $item->tindakan_perbaikan
        ]);

        if ($request->status === 'Selesai') {
            $inventaris = Inventaris::find($item->inventaris_id);
            if ($inventaris) {
                $inventaris->update(['kondisi' => 'Baik']);
            }
        }

        return back()->with('message', 'Status perbaikan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = LaporanKerusakan::findOrFail($id);
        $item->delete();

        return back()->with('message', 'Data laporan berhasil dihapus.');
    }
}
