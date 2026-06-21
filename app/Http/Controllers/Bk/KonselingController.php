<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Konseling;
use App\Models\Siswa;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class KonselingController extends Controller
{
    public function index()
    {
        $konseling = Konseling::with(['siswa.kelas', 'guru'])
            ->orderBy('tanggal_konseling', 'DESC')
            ->get();
            
        $siswa = Siswa::with('kelas')->orderBy('nama_lengkap', 'ASC')->get();

        return Inertia::render('Bk/Konseling/Index', [
            'konseling' => $konseling,
            'siswa' => $siswa
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:tbl_siswa,id',
            'jenis_konseling' => 'required|in:Pribadi,Sosial,Belajar,Karir',
            'topik' => 'required|string|max:255',
            'tanggal_konseling' => 'required|date',
            'status' => 'required|in:Selesai,Follow-Up'
        ]);

        Konseling::create([
            'siswa_id' => $request->siswa_id,
            'guru_id' => Auth::id() ?? 1,
            'tanggal_konseling' => $request->tanggal_konseling,
            'jenis_konseling' => $request->jenis_konseling,
            'topik' => $request->topik,
            'hasil_konseling' => $request->hasil_konseling,
            'tindak_lanjut' => $request->tindak_lanjut,
            'status' => $request->status
        ]);

        return back()->with('message', 'Catatan konseling berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis_konseling' => 'required|in:Pribadi,Sosial,Belajar,Karir',
            'topik' => 'required|string|max:255',
            'tanggal_konseling' => 'required|date',
            'status' => 'required|in:Selesai,Follow-Up'
        ]);

        $konseling = Konseling::findOrFail($id);
        $konseling->update([
            'tanggal_konseling' => $request->tanggal_konseling,
            'jenis_konseling' => $request->jenis_konseling,
            'topik' => $request->topik,
            'hasil_konseling' => $request->hasil_konseling,
            'tindak_lanjut' => $request->tindak_lanjut,
            'status' => $request->status
        ]);

        return back()->with('message', 'Catatan konseling berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $konseling = Konseling::findOrFail($id);
        $konseling->delete();

        return back()->with('message', 'Catatan konseling berhasil dihapus.');
    }
}
