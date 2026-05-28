<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Guru;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $jurusanFilter = $request->input('jurusan_id');

        $kelas = Kelas::with(['jurusan', 'waliKelas'])
            ->when($search, function ($query, $search) {
                $query->where('nama_kelas', 'like', "%{$search}%");
            })
            ->when($jurusanFilter, function ($query, $jurusanFilter) {
                $query->where('id_jurusan', $jurusanFilter);
            })
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $jurusans = Jurusan::select('id', 'nama_jurusan', 'kode_jurusan')->orderBy('nama_jurusan', 'asc')->get();
        $gurus = Guru::select('id', 'nama_lengkap', 'nip')->orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Admin/Master/Kelas/Index', [
            'kelas' => $kelas,
            'jurusans' => $jurusans,
            'gurus' => $gurus,
            'filters' => $request->only(['search', 'per_page', 'jurusan_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|integer|min:1|max:13',
            'id_jurusan' => 'nullable|exists:tbl_jurusan,id',
            'guru_id' => 'nullable|exists:tbl_guru,id',
            'dapodik_id' => 'nullable|string|max:50',
        ]);

        Kelas::create($validated);

        return back()->with('message', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|integer|min:1|max:13',
            'id_jurusan' => 'nullable|exists:tbl_jurusan,id',
            'guru_id' => 'nullable|exists:tbl_guru,id',
            'dapodik_id' => 'nullable|string|max:50',
        ]);

        $kelas->update($validated);

        return back()->with('message', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            Kelas::findOrFail($id)->delete();
            return back()->with('message', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
