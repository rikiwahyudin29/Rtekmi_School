<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\KokuTema;
use App\Models\KokuKegiatan;
use App\Models\KokuKelompok;
use App\Models\Guru;

class KokurikulerController extends Controller
{
    public function index()
    {
        $tema = KokuTema::with('kegiatan')->get();
        $kelompok = KokuKelompok::with('guru')->get();
        $guru = Guru::all();

        return Inertia::render('Admin/Kokurikuler/Index', [
            'tema' => $tema,
            'kelompok' => $kelompok,
            'guru' => $guru
        ]);
    }

    public function storeTema(Request $request)
    {
        $request->validate(['nama_tema' => 'required|string']);
        KokuTema::create($request->all());
        return redirect()->back()->with('success', 'Tema Kokurikuler berhasil ditambahkan.');
    }

    public function storeKegiatan(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|integer',
            'nama_kegiatan' => 'required|string',
            'deskripsi' => 'required|string'
        ]);
        KokuKegiatan::create($request->all());
        return redirect()->back()->with('success', 'Kegiatan Kokurikuler berhasil ditambahkan.');
    }

    public function storeKelompok(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|integer',
            'nama_kelompok' => 'required|string'
        ]);
        KokuKelompok::create($request->all());
        return redirect()->back()->with('success', 'Kelompok Kokurikuler berhasil ditambahkan.');
    }
}
