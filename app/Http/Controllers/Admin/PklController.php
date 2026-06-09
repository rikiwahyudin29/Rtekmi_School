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
