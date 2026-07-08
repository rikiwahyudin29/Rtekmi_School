<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TugasKbmController extends Controller
{
    private function getGuruId()
    {
        $guru = DB::table('tbl_guru')
            ->where('id_user', Auth::id())
            ->orWhere('user_id', Auth::id())
            ->first();
        return $guru ? $guru->id : Auth::id();
    }

    public function index()
    {
        $id_guru = $this->getGuruId();

        $tugas = DB::table('tbl_tugas')
            ->select(
                'tbl_tugas.*',
                'tbl_kelas.nama_kelas',
                'tbl_mapel.nama_mapel',
                DB::raw('(SELECT COUNT(*) FROM tbl_tugas_kumpul WHERE tbl_tugas_kumpul.tugas_id = tbl_tugas.id) as total_kumpul')
            )
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_tugas.kelas_id')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_tugas.mapel_id')
            ->where('tbl_tugas.guru_id', $id_guru)
            ->orderBy('tbl_tugas.created_at', 'DESC')
            ->get();

        $jadwalIds = DB::table('tbl_jadwal')->where('id_guru', $id_guru)->get();
        $kelasIds = $jadwalIds->pluck('id_kelas')->unique();
        $mapelIds = $jadwalIds->pluck('id_mapel')->unique();

        $kelas = DB::table('tbl_kelas')->whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();
        $mapel = DB::table('tbl_mapel')->whereIn('id', $mapelIds)->orderBy('nama_mapel')->get();

        return Inertia::render('Guru/ELearning/Tugas/Index', [
            'tugas' => $tugas,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }

    public function store(Request $request)
    {
        $id_guru = $this->getGuruId();

        $request->validate([
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'judul'    => 'required|string|max:255',
            'deadline' => 'required|date',
        ]);

        $namaFile = null;
        if ($request->hasFile('file_pendukung')) {
            $file     = $request->file('file_pendukung');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tugas'), $namaFile);
        }

        DB::table('tbl_tugas')->insert([
            'guru_id'        => $id_guru,
            'kelas_id'       => $request->kelas_id,
            'mapel_id'       => $request->mapel_id,
            'judul'          => $request->judul,
            'deskripsi'      => \App\Helpers\SecurityHelper::cleanRichText($request->deskripsi),
            'deadline'       => $request->deadline,
            'file_pendukung' => $namaFile,
            'status'         => 1,
            'created_at'     => now(),
        ]);

        return back()->with('success', 'Tugas berhasil diterbitkan!');
    }

    public function destroy($id)
    {
        $tugas = DB::table('tbl_tugas')->where('id', $id)->first();
        if ($tugas && $tugas->file_pendukung && file_exists(public_path('uploads/tugas/' . $tugas->file_pendukung))) {
            unlink(public_path('uploads/tugas/' . $tugas->file_pendukung));
        }
        DB::table('tbl_tugas_kumpul')->where('tugas_id', $id)->delete();
        DB::table('tbl_tugas')->where('id', $id)->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Halaman Koreksi / Hasil Pengumpulan Tugas
     */
    public function hasil($tugas_id)
    {
        $id_guru = $this->getGuruId();

        $tugas = DB::table('tbl_tugas')
            ->select('tbl_tugas.*', 'tbl_kelas.nama_kelas', 'tbl_mapel.nama_mapel')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_tugas.kelas_id')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_tugas.mapel_id')
            ->where('tbl_tugas.id', $tugas_id)
            ->where('tbl_tugas.guru_id', $id_guru)
            ->first();

        if (!$tugas) {
            return redirect()->route('guru.elearning.tugas.index')->with('error', 'Tugas tidak ditemukan.');
        }

        $siswa = DB::table('tbl_siswa')
            ->select('id', 'nama_lengkap', 'nis')
            ->where('kelas_id', $tugas->kelas_id)
            ->orderBy('nama_lengkap')
            ->get();

        $kumpul_raw = DB::table('tbl_tugas_kumpul')
            ->where('tugas_id', $tugas_id)
            ->get()
            ->keyBy('siswa_id');

        return Inertia::render('Guru/ELearning/Tugas/Hasil', [
            'tugas'       => $tugas,
            'siswa'       => $siswa,
            'pengumpulan' => $kumpul_raw,
        ]);
    }

    /**
     * Simpan Nilai Tugas
     */
    public function simpanNilai(Request $request)
    {
        $request->validate([
            'tugas_id' => 'required',
            'siswa_id' => 'required',
            'nilai'    => 'required|numeric|min:0|max:100',
        ]);

        $exists = DB::table('tbl_tugas_kumpul')
            ->where(['tugas_id' => $request->tugas_id, 'siswa_id' => $request->siswa_id])
            ->exists();

        if ($exists) {
            DB::table('tbl_tugas_kumpul')
                ->where(['tugas_id' => $request->tugas_id, 'siswa_id' => $request->siswa_id])
                ->update(['nilai' => $request->nilai, 'komentar_guru' => $request->komentar_guru]);
        } else {
            DB::table('tbl_tugas_kumpul')->insert([
                'tugas_id'      => $request->tugas_id,
                'siswa_id'      => $request->siswa_id,
                'nilai'         => $request->nilai,
                'komentar_guru' => $request->komentar_guru,
                'status_kumpul' => 'Terlambat',
            ]);
        }

        return back()->with('success', 'Nilai berhasil disimpan.');
    }
}
