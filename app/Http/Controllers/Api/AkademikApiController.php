<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AkademikApiController extends Controller
{
    // Helper cari ID Siswa dari NISN
    private function getSiswaInfo($nisn)
    {
        return DB::table('tbl_siswa')
            ->select('id', 'nama_lengkap', 'kelas_id')
            ->where('nisn', $nisn)
            ->first();
    }

    // =======================================================
    // 1. API RINGKASAN DASHBOARD (Tagihan, Presensi, Disiplin)
    // =======================================================
    public function getDashboardSummary(Request $request)
    {
        $nisn = $request->input('nisn');
        $siswa = $this->getSiswaInfo($nisn);

        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        // A. Hitung Total Tunggakan
        $tagihan_aktif = DB::table('tbl_tagihan')
            ->select('nominal_tagihan', 'nominal_terbayar')
            ->where('id_siswa', $siswa->id)
            ->where('status_bayar', '!=', 'LUNAS')
            ->get();

        $total_tunggakan = 0;
        foreach ($tagihan_aktif as $t) {
            $total_tunggakan += ((int)$t->nominal_tagihan - (int)$t->nominal_terbayar);
        }

        // B. Cek Poin Disiplin
        $rekap_bk = DB::table('tbl_siswa_pelanggaran as sp')
            ->select(DB::raw('IFNULL(SUM(p.poin), 0) as total_minus'))
            ->join('tbl_master_pelanggaran as p', 'p.id', '=', 'sp.pelanggaran_id')
            ->where('sp.siswa_id', $siswa->id)
            ->first();
            
        $poin_disiplin = 100 - ($rekap_bk->total_minus ?? 0);

        // C. Cek Tugas Aktif
        $tugas_aktif = DB::table('tbl_tugas')
            ->where('kelas_id', $siswa->kelas_id)
            ->where('status', 1)
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'keuangan' => $total_tunggakan,
                'tugas_aktif' => $tugas_aktif,
                'poin_disiplin' => $poin_disiplin
            ]
        ], 200);
    }

    // =======================================================
    // 2. API DAFTAR MATERI
    // =======================================================
    public function getMateri(Request $request)
    {
        $nisn = $request->input('nisn');
        $siswa = $this->getSiswaInfo($nisn);

        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        // Ambil materi sesuai kelas siswa
        $materi = DB::table('tbl_materi as tm')
            ->select('tm.*', 'mapel.nama_mapel', 'guru.nama_lengkap as nama_guru')
            ->join('tbl_mapel as mapel', 'mapel.id', '=', 'tm.mapel_id')
            ->leftJoin('tbl_guru as guru', 'guru.id', '=', 'tm.guru_id')
            ->where('tm.kelas_id', $siswa->kelas_id)
            ->orderBy('tm.created_at', 'DESC')
            ->get();

        return response()->json(['status' => true, 'data' => $materi], 200);
    }

    // =======================================================
    // 3. API DAFTAR TUGAS
    // =======================================================
    public function getTugas(Request $request)
    {
        $nisn = $request->input('nisn');
        $siswa = $this->getSiswaInfo($nisn);

        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        // Ambil tugas sesuai kelas siswa beserta status pengumpulannya
        $tugas = DB::table('tbl_tugas as tt')
            ->select(
                'tt.*', 
                'mapel.nama_mapel', 
                'guru.nama_lengkap as nama_guru',
                'kumpul.id as id_kumpul', 
                'kumpul.file_jawaban', 
                'kumpul.nilai', 
                'kumpul.komentar_guru', 
                'kumpul.tgl_kumpul', 
                'kumpul.status_kumpul', 
                'kumpul.catatan_siswa'
            )
            ->join('tbl_mapel as mapel', 'mapel.id', '=', 'tt.mapel_id')
            ->leftJoin('tbl_guru as guru', 'guru.id', '=', 'tt.guru_id')
            ->leftJoin('tbl_tugas_kumpul as kumpul', function ($join) use ($siswa) {
                $join->on('kumpul.tugas_id', '=', 'tt.id')
                     ->where('kumpul.siswa_id', '=', $siswa->id);
            })
            ->where('tt.kelas_id', $siswa->kelas_id)
            ->where('tt.status', 1)
            ->orderBy('tt.created_at', 'DESC')
            ->get();

        return response()->json(['status' => true, 'data' => $tugas], 200);
    }

    // =======================================================
    // 4. API SUBMIT TUGAS (UPLOAD FILE & CATATAN)
    // =======================================================
    public function submitTugas(Request $request)
    {
        $nisn     = $request->input('nisn');
        $tugas_id = $request->input('tugas_id');
        $catatan  = $request->input('catatan_siswa');

        $siswa = $this->getSiswaInfo($nisn);
        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        // Cek Deadline
        $tugasInfo = DB::table('tbl_tugas')->where('id', $tugas_id)->first();
        if (!$tugasInfo) {
            return response()->json(['status' => false, 'message' => 'Tugas tidak valid.'], 404);
        }

        $sekarang = date('Y-m-d H:i:s');
        $status_kumpul = ($sekarang > $tugasInfo->deadline) ? 'Terlambat' : 'Tepat Waktu';

        // Cek apakah ini update (sudah pernah kumpul sebelumnya)
        $cek = DB::table('tbl_tugas_kumpul')
            ->where(['tugas_id' => $tugas_id, 'siswa_id' => $siswa->id])
            ->first();

        $namaFile = $cek ? $cek->file_jawaban : null;

        // Handle File Upload dari Android (Multipart)
        if ($request->hasFile('file_jawaban') && $request->file('file_jawaban')->isValid()) {
            $file = $request->file('file_jawaban');
            
            // Hapus file lama jika ada agar server tidak penuh
            if ($cek && !empty($cek->file_jawaban)) {
                $oldPath = public_path('uploads/tugas_siswa/' . $cek->file_jawaban);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            
            // Simpan file baru
            $namaFile = $file->hashName(); // Menghasilkan nama acak + ekstensi
            $file->move(public_path('uploads/tugas_siswa'), $namaFile);
        }

        $data = [
            'tugas_id'      => $tugas_id,
            'siswa_id'      => $siswa->id,
            'catatan_siswa' => $catatan,
            'file_jawaban'  => $namaFile,
            'tgl_kumpul'    => $sekarang,
            'status_kumpul' => $status_kumpul
        ];

        if ($cek) {
            DB::table('tbl_tugas_kumpul')->where('id', $cek->id)->update($data);
            $msg = 'Jawaban berhasil diperbarui!';
        } else {
            DB::table('tbl_tugas_kumpul')->insert($data);
            $msg = 'Jawaban berhasil dikirim!';
        }

        return response()->json(['status' => true, 'message' => $msg], 200);
    }
}
