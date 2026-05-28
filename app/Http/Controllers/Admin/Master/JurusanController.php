<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Guru;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $jurusan = Jurusan::with('kepalaJurusan')
            ->when($search, function ($query, $search) {
                $query->where('nama_jurusan', 'like', "%{$search}%")
                      ->orWhere('kode_jurusan', 'like', "%{$search}%");
            })
            ->orderBy('nama_jurusan', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $gurus = Guru::select('id', 'nama_lengkap', 'nip')->orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Admin/Master/Jurusan/Index', [
            'jurusan' => $jurusan,
            'gurus' => $gurus,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:10',
            'nama_jurusan' => 'required|string|max:100',
            'kepala_jurusan_id' => 'nullable|exists:tbl_guru,id',
            'dapodik_id' => 'nullable|string|max:50',
        ]);

        Jurusan::create($validated);

        return back()->with('message', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:10',
            'nama_jurusan' => 'required|string|max:100',
            'kepala_jurusan_id' => 'nullable|exists:tbl_guru,id',
            'dapodik_id' => 'nullable|string|max:50',
        ]);

        $jurusan->update($validated);

        return back()->with('message', 'Jurusan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Jurusan::findOrFail($id)->delete();
            return back()->with('message', 'Jurusan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
