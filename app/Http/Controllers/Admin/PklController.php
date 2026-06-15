<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PklKelompok;
use App\Models\Guru;
use App\Models\Dudi;

class PklController extends Controller
{
    // ==========================================
    // 1. MASTER DUDI
    // ==========================================
    public function index()
    {
        $dudi = Dudi::orderBy('nama_dudi', 'asc')->get();

        return Inertia::render('Admin/Pkl/Dudi', [
            'dudi' => $dudi
        ]);
    }

    public function simpanDudi(Request $request)
    {
        $request->validate([
            'nama_dudi' => 'required|string',
            'bidang_usaha' => 'required|string',
            'nama_pimpinan' => 'required|string',
            'alamat_lengkap' => 'required|string',
            'radius_absen' => 'required|integer'
        ]);

        Dudi::create($request->all());

        return redirect()->route('admin.pkl.index')->with('message', 'Data DU/DI berhasil ditambahkan!');
    }

    public function updateDudi(Request $request)
    {
        $id = $request->input('id');
        $request->validate([
            'nama_dudi' => 'required|string',
            'bidang_usaha' => 'required|string',
            'nama_pimpinan' => 'required|string',
            'alamat_lengkap' => 'required|string',
            'radius_absen' => 'required|integer'
        ]);

        $dudi = Dudi::findOrFail($id);
        $dudi->update($request->all());

        return redirect()->route('admin.pkl.index')->with('message', 'Data DU/DI berhasil diperbarui!');
    }

    public function deleteDudi($id)
    {
        $dudi = Dudi::findOrFail($id);
        $dudi->delete();
        return redirect()->route('admin.pkl.index')->with('message', 'Data DU/DI berhasil dihapus!');
    }

    // ==========================================
    // 2. MAPPING KELOMPOK
    // ==========================================
    public function kelompok()
    {
        $kelompok = PklKelompok::with(['guru', 'dudi'])->get();
        $guru = Guru::all();
        $dudi = Dudi::all();

        return Inertia::render('Admin/Pkl/Kelompok', [
            'kelompok' => $kelompok,
            'guru' => $guru,
            'dudi' => $dudi
        ]);
    }

    public function storeKelompok(Request $request)
    {
        $request->validate([
            'nama_kelompok' => 'required|string',
            'guru_id' => 'required|integer',
            'dudi_id' => 'required|integer'
        ]);

        PklKelompok::create($request->all());

        return redirect()->back()->with('success', 'Kelompok PKL berhasil ditambahkan.');
    }
}
