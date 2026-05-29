<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\PosBayar;
use App\Models\JenisBayar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosBayarController extends Controller
{
    public function index()
    {
        $pos = PosBayar::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Keuangan/Pos/Index', [
            'pos' => $pos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pos' => 'required|string|max:100',
            'keterangan' => 'nullable|string'
        ]);

        PosBayar::create($request->only('nama_pos', 'keterangan'));

        return back()->with('message', 'Data Pos Bayar berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pos' => 'required|string|max:100',
            'keterangan' => 'nullable|string'
        ]);

        $pos = PosBayar::findOrFail($id);
        $pos->update($request->only('nama_pos', 'keterangan'));

        return back()->with('message', 'Data Pos Bayar berhasil diperbarui');
    }

    public function destroy($id)
    {
        $pos = PosBayar::findOrFail($id);
        
        $terpakai = JenisBayar::where('id_pos_bayar', $id)->count();
        if ($terpakai > 0) {
            return back()->with('error', 'Gagal hapus! Pos ini sudah digunakan dalam setting jenis pembayaran.');
        }

        $pos->delete();
        return back()->with('message', 'Data Pos Bayar berhasil dihapus');
    }
}
