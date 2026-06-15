<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EkskulController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $ekskul = Ekskul::when($search, function($q) use ($search) {
            $q->where('nama_ekskul', 'like', "%{$search}%");
        })->orderBy('nama_ekskul', 'asc')->get();

        return Inertia::render('Admin/Master/Ekskul/Index', [
            'ekskul' => $ekskul,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ekskul' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        Ekskul::create($request->all());

        return redirect()->back()->with('success', 'Data Ekstrakurikuler berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $ekskul = Ekskul::findOrFail($id);

        $request->validate([
            'nama_ekskul' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        $ekskul->update($request->all());

        return redirect()->back()->with('success', 'Data Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $ekskul = Ekskul::findOrFail($id);
        
        // Delete related ekskul_nilai or handle integrity if necessary
        // \App\Models\EkskulNilai::where('ekskul_id', $id)->delete();
        
        $ekskul->delete();

        return redirect()->back()->with('success', 'Data Ekstrakurikuler berhasil dihapus.');
    }
}
