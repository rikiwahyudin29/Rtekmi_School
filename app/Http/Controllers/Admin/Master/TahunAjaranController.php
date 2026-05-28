<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $tahunAjaran = TahunAjaran::when($search, function ($query, $search) {
                $query->where('tahun_ajaran', 'like', "%{$search}%");
            })
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Master/TahunAjaran/Index', [
            'tahun_ajaran' => $tahunAjaran,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran' => 'required|string|max:10',
            'semester' => 'required|in:Ganjil,Genap',
            'status' => 'nullable|in:Aktif,Nonaktif',
        ]);

        DB::beginTransaction();
        try {
            if (isset($validated['status']) && $validated['status'] === 'Aktif') {
                TahunAjaran::query()->update(['status' => 'Nonaktif']);
            }

            TahunAjaran::create($validated);
            DB::commit();

            return back()->with('message', 'Tahun Ajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $tahun = TahunAjaran::findOrFail($id);

        $validated = $request->validate([
            'tahun_ajaran' => 'required|string|max:10',
            'semester' => 'required|in:Ganjil,Genap',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['status'] === 'Aktif') {
                TahunAjaran::where('id', '!=', $id)->update(['status' => 'Nonaktif']);
            }

            $tahun->update($validated);
            DB::commit();

            return back()->with('message', 'Tahun Ajaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            TahunAjaran::findOrFail($id)->delete();
            return back()->with('message', 'Tahun Ajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
