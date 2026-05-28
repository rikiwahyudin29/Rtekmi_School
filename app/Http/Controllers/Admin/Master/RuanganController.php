<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $ruangan = Ruangan::when($search, function ($query, $search) {
                $query->where('nama_ruangan', 'like', "%{$search}%");
            })
            ->orderBy('nama_ruangan', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Master/Ruangan/Index', [
            'ruangan' => $ruangan,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:50',
        ]);

        Ruangan::create($validated);

        return back()->with('message', 'Ruangan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:50',
        ]);

        $ruangan->update($validated);

        return back()->with('message', 'Ruangan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Ruangan::findOrFail($id)->delete();
            return back()->with('message', 'Ruangan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
