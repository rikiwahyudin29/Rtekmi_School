<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class MateriController extends Controller
{
    private function getGuruId()
    {
        $guru = DB::table('tbl_guru')
            ->where('id_user', Auth::id())
            ->orWhere('user_id', Auth::id())
            ->first();
        return $guru ? $guru->id : Auth::id();
    }

    public function index(Request $request)
    {
        $id_guru  = $this->getGuruId();
        $search   = $request->get('search');
        $kelas_id = $request->get('kelas_id');

        $query = DB::table('tbl_materi')
            ->select('tbl_materi.*', 'tbl_kelas.nama_kelas', 'tbl_mapel.nama_mapel')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_materi.kelas_id')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_materi.mapel_id')
            ->where('tbl_materi.guru_id', $id_guru);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('tbl_materi.judul', 'like', "%$search%")
                  ->orWhere('tbl_mapel.nama_mapel', 'like', "%$search%");
            });
        }
        if ($kelas_id) {
            $query->where('tbl_materi.kelas_id', $kelas_id);
        }

        $materi = $query->orderBy('tbl_materi.created_at', 'DESC')->get();
        $jadwalIds = DB::table('tbl_jadwal')->where('id_guru', $id_guru)->get();
        $kelasIds = $jadwalIds->pluck('id_kelas')->unique();
        $mapelIds = $jadwalIds->pluck('id_mapel')->unique();

        $kelas  = DB::table('tbl_kelas')->whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();
        $mapel  = DB::table('tbl_mapel')->whereIn('id', $mapelIds)->orderBy('nama_mapel')->get();

        return Inertia::render('Guru/ELearning/Materi/Index', [
            'materi'    => $materi,
            'kelas'     => $kelas,
            'mapel'     => $mapel,
            'filters'   => ['search' => $search, 'kelas_id' => $kelas_id],
        ]);
    }

    public function store(Request $request)
    {
        $id_guru = $this->getGuruId();

        $request->validate([
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'judul'    => 'required|string|max:255',
        ]);

        $namaFile = null;
        if ($request->hasFile('file_materi')) {
            $file     = $request->file('file_materi');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/materi'), $namaFile);
        }

        DB::table('tbl_materi')->insert([
            'guru_id'      => $id_guru,
            'kelas_id'     => $request->kelas_id,
            'mapel_id'     => $request->mapel_id,
            'judul'        => $request->judul,
            'deskripsi'    => $request->deskripsi,
            'link_youtube' => $request->link_youtube,
            'file_materi'  => $namaFile,
            'created_at'   => now(),
        ]);

        return back()->with('success', 'Materi berhasil diupload!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'judul'    => 'required|string|max:255',
        ]);

        $data = [
            'kelas_id'     => $request->kelas_id,
            'mapel_id'     => $request->mapel_id,
            'judul'        => $request->judul,
            'deskripsi'    => $request->deskripsi,
            'link_youtube' => $request->link_youtube,
            'updated_at'   => now(),
        ];

        if ($request->hasFile('file_materi')) {
            // Hapus file lama
            $old = DB::table('tbl_materi')->where('id', $id)->first();
            if ($old && $old->file_materi && file_exists(public_path('uploads/materi/' . $old->file_materi))) {
                unlink(public_path('uploads/materi/' . $old->file_materi));
            }
            $file = $request->file('file_materi');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/materi'), $namaFile);
            $data['file_materi'] = $namaFile;
        }

        DB::table('tbl_materi')->where('id', $id)->update($data);
        return back()->with('success', 'Materi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $materi = DB::table('tbl_materi')->where('id', $id)->first();
        if ($materi && $materi->file_materi && file_exists(public_path('uploads/materi/' . $materi->file_materi))) {
            unlink(public_path('uploads/materi/' . $materi->file_materi));
        }
        DB::table('tbl_materi')->where('id', $id)->delete();
        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
