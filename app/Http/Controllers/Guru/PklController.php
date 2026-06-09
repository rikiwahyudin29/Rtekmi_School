<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PklKelompok;
use App\Models\PklTp;
use Illuminate\Support\Facades\Auth;

class PklController extends Controller
{
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $kelompok = PklKelompok::with('dudi')->where('guru_id', $guru_id)->get();
        
        $selected_kelompok_id = request('kelompok_id', $kelompok->first()->id ?? null);
        $tp_list = [];
        
        if ($selected_kelompok_id) {
            $tp_list = PklTp::where('kelompok_id', $selected_kelompok_id)->get();
        }

        return Inertia::render('Guru/Pkl/Index', [
            'kelompok' => $kelompok,
            'tp_list' => $tp_list,
            'selected_kelompok_id' => $selected_kelompok_id
        ]);
    }

    public function storeTp(Request $request)
    {
        $request->validate([
            'kelompok_id' => 'required|integer',
            'kode_tp' => 'required|string',
            'deskripsi' => 'required|string'
        ]);

        PklTp::create($request->all());

        return redirect()->back()->with('success', 'Tujuan Pembelajaran PKL berhasil ditambahkan.');
    }
}
