<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterPelanggaran;
use Inertia\Inertia;

class MasterPelanggaranController extends Controller
{
    public function index()
    {
        $master = MasterPelanggaran::orderBy('poin', 'DESC')->get();
        return Inertia::render('Bk/Pelanggaran/Master', [
            'master' => $master
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'poin' => 'required|integer|min:1'
        ]);

        MasterPelanggaran::create($request->only('nama_pelanggaran', 'kategori', 'poin'));

        return back()->with('message', 'Master pelanggaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'poin' => 'required|integer|min:1'
        ]);

        $pelanggaran = MasterPelanggaran::findOrFail($id);
        $pelanggaran->update($request->only('nama_pelanggaran', 'kategori', 'poin'));

        return back()->with('message', 'Master pelanggaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pelanggaran = MasterPelanggaran::findOrFail($id);
        $pelanggaran->delete();

        return back()->with('message', 'Master pelanggaran berhasil dihapus.');
    }
}
