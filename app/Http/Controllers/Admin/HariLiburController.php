<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HariLiburController extends Controller
{
    public function index()
    {
        $libur = HariLibur::orderBy('tanggal', 'desc')->get();
        return Inertia::render('Admin/Presensi/HariLibur', [
            'libur' => $libur
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();

        return redirect()->back()->with('success', 'Hari libur berhasil dihapus!');
    }
}
