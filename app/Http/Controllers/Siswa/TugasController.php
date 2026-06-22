<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Tugas;
use App\Models\TugasKumpul;
use Illuminate\Support\Facades\File;

class TugasController extends Controller
{
    public function index()
    {
        $siswa = Auth::user()->siswa;
        
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Sesi siswa tidak ditemukan.');
        }

        $tugas = Tugas::with(['mapel', 'guru', 'kumpul' => function($query) use ($siswa) {
            $query->where('siswa_id', $siswa->id);
        }])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $kumpul = $item->kumpul->first();
                $item->jawaban_saya = $kumpul;
                return $item;
            });

        return Inertia::render('Siswa/Tugas/Index', [
            'tugas' => $tugas
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'tugas_id' => 'required|exists:tbl_tugas,id',
            'file_jawaban' => 'nullable|file',
            'catatan_siswa' => 'nullable|string'
        ]);

        $siswa = Auth::user()->siswa;
        $tugas = Tugas::findOrFail($request->tugas_id);
        
        $sekarang = now();
        $deadline = \Carbon\Carbon::parse($tugas->deadline);
        $status_kumpul = ($sekarang > $deadline) ? 'Terlambat' : 'Tepat Waktu';

        $kumpul = TugasKumpul::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $namaFile = $kumpul ? $kumpul->file_jawaban : null;

        if ($request->hasFile('file_jawaban')) {
            $file = $request->file('file_jawaban');
            
            if ($kumpul && $kumpul->file_jawaban && File::exists(public_path('uploads/tugas_siswa/' . $kumpul->file_jawaban))) {
                File::delete(public_path('uploads/tugas_siswa/' . $kumpul->file_jawaban));
            }
            
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tugas_siswa'), $namaFile);
        }

        $data = [
            'tugas_id' => $tugas->id,
            'siswa_id' => $siswa->id,
            'file_jawaban' => $namaFile,
            'catatan_siswa' => $request->catatan_siswa,
            'tgl_kumpul' => $sekarang,
            'status_kumpul' => $status_kumpul
        ];

        if ($kumpul) {
            $kumpul->update($data);
            $msg = 'Jawaban berhasil diperbarui!';
        } else {
            TugasKumpul::create($data);
            $msg = 'Jawaban berhasil dikirim!';
        }

        return redirect()->back()->with('success', $msg);
    }
}
