<?php

namespace App\Http\Controllers\Admin\Sarpras;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventaris;
use Inertia\Inertia;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventaris::query();

        // Pencarian (Search Filter)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        // Kategori Filter
        if ($request->has('kategori') && $request->kategori != 'Semua') {
            $query->where('kategori', $request->kategori);
        }

        $inventaris = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Statistik
        $stats = [
            'total' => Inventaris::count(),
            'baik' => Inventaris::where('kondisi', 'Baik')->count(),
            'rusak' => Inventaris::whereIn('kondisi', ['Rusak Ringan', 'Rusak Berat'])->count(),
        ];

        $kategoriList = Inventaris::select('kategori')->distinct()->whereNotNull('kategori')->pluck('kategori')->toArray();

        return Inertia::render('Admin/Sarpras/Inventaris/Index', [
            'inventaris' => $inventaris,
            'stats' => $stats,
            'kategoriList' => $kategoriList,
            'filters' => $request->only(['search', 'kategori'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'nama_barang' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
            'lokasi' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
            'kondisi' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'tgl_masuk' => 'required|date',
        ]);

        $data = $request->all();
        
        // Auto-generate kode_barang if empty
        if (empty($data['kode_barang'])) {
            $lastId = Inventaris::max('id') ?? 0;
            $nextId = $lastId + 1;
            $data['kode_barang'] = 'INV/' . date('Y/m/') . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        Inventaris::create($data);

        return back()->with('message', 'Aset baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'nama_barang' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
            'lokasi' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
            'kondisi' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'tgl_masuk' => 'required|date',
        ]);

        $item = Inventaris::findOrFail($id);
        $data = $request->all();
        
        if (empty($data['kode_barang'])) {
            $data['kode_barang'] = $item->kode_barang ?? ('INV/' . date('Y/m/') . str_pad($item->id, 4, '0', STR_PAD_LEFT));
        }

        $item->update($data);

        return back()->with('message', 'Data aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = Inventaris::findOrFail($id);
        $item->delete();

        return back()->with('message', 'Data aset berhasil dihapus secara permanen.');
    }

    public function printLabel(Request $request)
    {
        $query = Inventaris::query();

        // Check if specific IDs are selected (for single or multi selection)
        if ($request->has('ids') && $request->ids != '') {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        } else {
            // Apply current filters for mass printing
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_barang', 'like', "%{$search}%")
                      ->orWhere('kode_barang', 'like', "%{$search}%")
                      ->orWhere('lokasi', 'like', "%{$search}%");
                });
            }
    
            if ($request->has('kategori') && $request->kategori != 'Semua') {
                $query->where('kategori', $request->kategori);
            }
        }

        $items = $query->get();

        return Inertia::render('Admin/Sarpras/Inventaris/PrintLabel', [
            'items' => $items,
            'sekolah' => \App\Models\Sekolah::first(),
            'appUrl' => url('/')
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = Inventaris::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        if ($request->has('kategori') && $request->kategori != 'Semua') {
            $query->where('kategori', $request->kategori);
        }

        $items = $query->orderBy('id', 'desc')->get();
        $sekolah = \App\Models\Sekolah::first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.sarpras.inventaris.pdf', compact('items', 'sekolah'))
               ->setPaper('a4', 'landscape');

        return $pdf->download('Data_Inventaris_Aset.pdf');
    }
}
