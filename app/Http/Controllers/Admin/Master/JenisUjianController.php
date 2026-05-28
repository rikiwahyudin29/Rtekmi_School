<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\JenisUjian;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JenisUjianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $jenisUjian = JenisUjian::when($search, function ($query, $search) {
                $query->where('nama_jenis', 'like', "%{$search}%")
                      ->orWhere('kode_jenis', 'like', "%{$search}%");
            })
            ->orderBy('nama_jenis', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Master/JenisUjian/Index', [
            'jenis_ujian' => $jenisUjian,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:50',
            'kode_jenis' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
        ]);

        JenisUjian::create([
            'nama_jenis' => $validated['nama_jenis'],
            'kode_jenis' => $validated['kode_jenis'] ?? null,
            'status' => $validated['status'] ?? true,
        ]);

        return back()->with('message', 'Jenis Ujian berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jenis = JenisUjian::findOrFail($id);

        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:50',
            'kode_jenis' => 'nullable|string|max:20',
            'status' => 'required|boolean',
        ]);

        $jenis->update($validated);

        return back()->with('message', 'Jenis Ujian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            JenisUjian::findOrFail($id)->delete();
            return back()->with('message', 'Jenis Ujian berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
