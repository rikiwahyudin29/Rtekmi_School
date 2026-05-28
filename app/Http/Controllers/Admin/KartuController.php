<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class KartuController extends Controller
{
    public function index()
    {
        $kelas = DB::table('tbl_kelas')->orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Admin/Presensi/Kartu/Index', [
            'kelas' => $kelas
        ]);
    }

    public function cetakSiswa(Request $request)
    {
        $id_kelas = $request->query('id_kelas');
        if (!$id_kelas) return redirect()->route('admin.presensi.kartu.index');

        $siswa = DB::table('tbl_siswa')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_siswa.kelas_id', $id_kelas)
            ->select('tbl_siswa.*', 'tbl_kelas.nama_kelas')
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')
            ->get();

        $kelas = DB::table('tbl_kelas')->where('id', $id_kelas)->first();

        // Identitas Sekolah
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first();

        return view('admin.kartu.cetak', [
            'tipe' => 'siswa',
            'peserta' => $siswa,
            'kelas' => $kelas->nama_kelas ?? '',
            'sekolah' => $sekolah
        ]);
    }

    public function cetakGuru()
    {
        $guru = DB::table('tbl_guru')->orderBy('nama_guru', 'asc')->get();
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first();

        return view('admin.kartu.cetak', [
            'tipe' => 'guru',
            'peserta' => $guru,
            'kelas' => 'GURU & STAFF',
            'sekolah' => $sekolah
        ]);
    }

    public function simpanUid(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'tipe' => 'required|in:siswa,guru',
            'uid' => 'required'
        ]);

        $uid = $request->uid;

        // Check UID conflict
        if ($request->tipe === 'siswa') {
            $cek = DB::table('tbl_siswa')->where('rfid_uid', $uid)->where('id', '!=', $request->id_user)->count();
            if ($cek > 0) return response()->json(['status' => 'error', 'message' => 'Kartu sudah dipakai siswa lain']);

            DB::table('tbl_siswa')->where('id', $request->id_user)->update(['rfid_uid' => $uid, 'qr_code' => $uid]);
        } else {
            $cek = DB::table('tbl_guru')->where('rfid_uid', $uid)->where('id', '!=', $request->id_user)->count();
            if ($cek > 0) return response()->json(['status' => 'error', 'message' => 'Kartu sudah dipakai guru lain']);

            DB::table('tbl_guru')->where('id', $request->id_user)->update(['rfid_uid' => $uid]);
        }

        return response()->json(['status' => 'success', 'message' => 'Kartu berhasil diregistrasi']);
    }
}
