<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Materi;

class MateriController extends Controller
{
    public function index()
    {
        $siswa = Auth::user()->siswa;
        
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Sesi siswa tidak ditemukan.');
        }

        $materi = Materi::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Siswa/Materi/Index', [
            'materi' => $materi
        ]);
    }
}
