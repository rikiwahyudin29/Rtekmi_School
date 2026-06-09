<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\P5Tema;
use App\Models\P5Projek;
use App\Models\P5Kelompok;
use App\Models\Guru;
use App\Models\Kelas;

class P5Controller extends Controller
{
    public function index()
    {
        $temas = P5Tema::with('projek')->get();
        return Inertia::render('Admin/P5/Index', [
            'temas' => $temas
        ]);
    }

    public function storeTema(Request $request)
    {
        $request->validate([
            'nama_tema' => 'required|string',
            'deskripsi' => 'nullable|string'
        ]);

        P5Tema::create([
            'nama_tema' => $request->nama_tema,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->back()->with('success', 'Tema P5 berhasil ditambahkan');
    }

    public function kelompok()
    {
        $kelompok = P5Kelompok::with(['projek.tema', 'koordinator'])->get();
        $gurus = Guru::all();
        $kelas = Kelas::all();
        $projeks = P5Projek::with('tema')->get();

        return Inertia::render('Admin/P5/Kelompok', [
            'kelompok' => $kelompok,
            'gurus' => $gurus,
            'kelas' => $kelas,
            'projeks' => $projeks
        ]);
    }

    public function storeKelompok(Request $request)
    {
        $request->validate([
            'nama_kelompok' => 'required|string',
            'projek_id' => 'required|integer',
            'guru_koordinator_id' => 'required|integer',
            'kelas_id_list' => 'required|array'
        ]);

        P5Kelompok::create([
            'nama_kelompok' => $request->nama_kelompok,
            'projek_id' => $request->projek_id,
            'guru_koordinator_id' => $request->guru_koordinator_id,
            'kelas_id_list' => implode(',', $request->kelas_id_list)
        ]);

        return redirect()->back()->with('success', 'Kelompok P5 berhasil ditambahkan');
    }
}
