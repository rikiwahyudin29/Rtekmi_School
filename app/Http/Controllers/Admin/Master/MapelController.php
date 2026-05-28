<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}
