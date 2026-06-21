<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TracerFrontController extends Controller
{
    public function index()
    {
        // Data Sekolah
        $web = DB::table('tbl_sekolah')->where('id', 1)->first();
        
        // Ambil semua pertanyaan kuesioner
        $pertanyaan = DB::table('tbl_tracer_pertanyaan')->get();

        return Inertia::render('Front/Tracer', [
            'web' => $web,
            'pertanyaan' => $pertanyaan
        ]);
    }

    public function cekNisn(Request $request)
    {
        $nisn = $request->input('nisn');
        
        // 1. Cek apakah siswa ada dan berstatus Lulus
        $alumni = DB::table('tbl_siswa')
            ->where('nisn', $nisn)
            ->where('status_siswa', 'Lulus')
            ->first();

        if (!$alumni) {
            return response()->json([
                'status' => 'error', 
                'message' => 'NISN tidak ditemukan atau belum berstatus Lulus/Alumni!'
            ], 404);
        }

        // 2. Cek apakah sudah pernah mengisi
        $cek_isi = DB::table('tbl_tracer_responden')->where('siswa_id', $alumni->id)->count();
        if ($cek_isi > 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Terima kasih, Anda sudah pernah mengisi form Tracer Study ini sebelumnya.'
            ], 400);
        }

        return response()->json([
            'status'         => 'success', 
            'nama_lengkap'   => strtoupper($alumni->nama_lengkap),
            'tahun_angkatan' => $alumni->tahun_angkatan ?? '-'
        ]);
    }

    public function simpan(Request $request)
    {
        $nisn = $request->input('nisn');
        $status_kegiatan = $request->input('status_kegiatan');
        $nama_instansi = $request->input('nama_instansi');

        $alumni = DB::table('tbl_siswa')
            ->where('nisn', $nisn)
            ->where('status_siswa', 'Lulus')
            ->first();

        if (!$alumni) {
            return back()->with('error', 'NISN tidak ditemukan atau belum dinyatakan Lulus/Alumni!');
        }

        $cek_isi = DB::table('tbl_tracer_responden')->where('siswa_id', $alumni->id)->count();
        if ($cek_isi > 0) {
            return back()->with('error', 'Terima kasih, Anda sudah pernah mengisi Tracer Study sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $responden_id = DB::table('tbl_tracer_responden')->insertGetId([
                'siswa_id'        => $alumni->id,
                'status_kegiatan' => $status_kegiatan,
                'nama_instansi'   => $nama_instansi,
                'tanggal_isi'     => date('Y-m-d H:i:s')
            ]);

            $pertanyaan = DB::table('tbl_tracer_pertanyaan')->get();
            foreach ($pertanyaan as $p) {
                // Di Vue kita post data jawaban per ID: jawaban[ID]
                $jawaban = $request->input('jawaban.' . $p->id);
                
                if (is_array($jawaban)) {
                    $jawaban = implode(', ', $jawaban);
                }

                if (!empty($jawaban)) {
                    DB::table('tbl_tracer_jawaban')->insert([
                        'responden_id'  => $responden_id,
                        'pertanyaan_id' => $p->id,
                        'jawaban'       => (string) $jawaban
                    ]);
                }
            }

            DB::commit();
            return back()->with('message', 'Terima kasih ' . $alumni->nama_lengkap . '! Data Tracer Study Anda berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage());
        }
    }
}
