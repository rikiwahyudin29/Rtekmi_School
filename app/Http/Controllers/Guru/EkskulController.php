<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EkskulController extends Controller
{
    private function getGuruId()
    {
        $user = Auth::user();
        $guru = DB::table('tbl_guru')->where('user_id', $user->id)->first();
        return $guru ? $guru->id : 0;
    }

    public function index()
    {
        $guru_id = $this->getGuruId();

        // Ambil daftar ekskul dimana guru ini sebagai pembina
        $ekskuls = DB::table('tbl_ekskul_pembina')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_pembina.ekskul_id')
            ->where('tbl_ekskul_pembina.guru_id', $guru_id)
            ->select('tbl_ekskul.*', 'tbl_ekskul_pembina.id as pembina_id')
            ->get();

        foreach ($ekskuls as $e) {
            $e->jml_anggota = DB::table('tbl_ekskul_anggota')
                ->where('ekskul_id', $e->id)
                ->where('status_anggota', 'Approved')
                ->count();
                
            $e->jml_pending = DB::table('tbl_ekskul_anggota')
                ->where('ekskul_id', $e->id)
                ->where('status_anggota', 'Pending')
                ->count();
                
            $e->total_jurnal = DB::table('tbl_ekskul_jurnal')
                ->where('ekskul_id', $e->id)
                ->count();
                
            $e->total_prestasi = DB::table('tbl_ekskul_prestasi')
                ->where('ekskul_id', $e->id)
                ->count();
        }

        return Inertia::render('Guru/Ekskul/Dashboard', [
            'ekskuls' => $ekskuls
        ]);
    }

    public function anggota($id)
    {
        $ekskul = DB::table('tbl_ekskul')->where('id', $id)->first();
        if(!$ekskul) abort(404);

        $anggota = DB::table('tbl_ekskul_anggota')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_anggota.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_anggota.ekskul_id', $id)
            ->select('tbl_ekskul_anggota.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_siswa.foto', 'tbl_kelas.nama_kelas')
            ->orderBy('tbl_ekskul_anggota.status_anggota', 'desc')
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')
            ->get();

        $kelas = DB::table('tbl_kelas')->orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Guru/Ekskul/Anggota', [
            'ekskul' => $ekskul,
            'anggota' => $anggota,
            'kelas' => $kelas
        ]);
    }

    public function validasiAnggota(Request $request)
    {
        $id_anggota = $request->input('id');
        $status = $request->input('status'); // Approved, Rejected, Dikeluarkan

        DB::table('tbl_ekskul_anggota')->where('id', $id_anggota)->update([
            'status_anggota' => $status
        ]);

        return back()->with('message', "Status anggota berhasil diubah menjadi {$status}!");
    }

    public function getCalonAnggota(Request $request, $ekskul_id)
    {
        $query = DB::table('tbl_siswa')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->whereNotIn('tbl_siswa.id', function($q) use ($ekskul_id) {
                $q->select('siswa_id')->from('tbl_ekskul_anggota')->where('ekskul_id', $ekskul_id);
            })
            ->where('tbl_siswa.status_siswa', 'Aktif')
            ->select('tbl_siswa.id', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_siswa.foto', 'tbl_kelas.nama_kelas');

        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $query->where('tbl_siswa.kelas_id', $request->kelas_id);
        }

        $siswa = $query->orderBy('tbl_siswa.nama_lengkap', 'asc')->get();

        return response()->json(['siswa' => $siswa]);
    }

    public function tambahManual(Request $request)
    {
        $request->validate([
            'ekskul_id' => 'required',
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'integer'
        ]);

        $dataInsert = [];
        $waktu = date('Y-m-d');
        foreach ($request->siswa_ids as $siswa_id) {
            // Pastikan belum anggota untuk mencegah duplikasi jika request dikirim berkali-kali
            $cek = DB::table('tbl_ekskul_anggota')->where('ekskul_id', $request->ekskul_id)->where('siswa_id', $siswa_id)->exists();
            if (!$cek) {
                $dataInsert[] = [
                    'ekskul_id' => $request->ekskul_id,
                    'siswa_id' => $siswa_id,
                    'tgl_daftar' => $waktu,
                    'status_anggota' => 'Approved'
                ];
            }
        }

        if (count($dataInsert) > 0) {
            DB::table('tbl_ekskul_anggota')->insert($dataInsert);
            return back()->with('message', count($dataInsert) . ' Siswa berhasil ditambahkan sebagai anggota aktif.');
        }

        return back()->with('message', 'Tidak ada siswa baru yang ditambahkan.');
    }

    public function templateExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'NIS / NISN');
        $sheet->setCellValue('B1', 'NAMA SISWA (Opsional, Sistem hanya baca NIS)');
        
        // Contoh data
        $sheet->setCellValue('A2', '12345678');
        $sheet->setCellValue('B2', 'Budi Santoso');
        
        // Styling header
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(40);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $fileName = 'Template_Import_Anggota_Ekskul.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'ekskul_id' => 'required',
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $berhasil = 0;
        $gagal = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header

            $nis = $row[0];
            if (empty($nis)) continue;

            $siswa = DB::table('tbl_siswa')->where('nis', $nis)->orWhere('nisn', $nis)->first();
            
            if ($siswa) {
                $cek = DB::table('tbl_ekskul_anggota')->where('ekskul_id', $request->ekskul_id)->where('siswa_id', $siswa->id)->first();
                if (!$cek) {
                    DB::table('tbl_ekskul_anggota')->insert([
                        'ekskul_id' => $request->ekskul_id,
                        'siswa_id' => $siswa->id,
                        'tgl_daftar' => date('Y-m-d'),
                        'status_anggota' => 'Approved'
                    ]);
                    $berhasil++;
                } else {
                    $gagal++;
                }
            } else {
                $gagal++;
            }
        }

        return back()->with('message', "Import selesai. Berhasil ditambahkan: {$berhasil} siswa. Gagal/Sudah ada: {$gagal} siswa.");
    }

    public function jurnal($id)
    {
        $ekskul = DB::table('tbl_ekskul')->where('id', $id)->first();
        if(!$ekskul) abort(404);

        $jurnals = DB::table('tbl_ekskul_jurnal')
            ->where('ekskul_id', $id)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung rekap absen per jurnal
        foreach ($jurnals as $j) {
            $j->hadir = DB::table('tbl_ekskul_absen')->where('jurnal_id', $j->id)->where('status_hadir', 'Hadir')->count();
            $j->sakit = DB::table('tbl_ekskul_absen')->where('jurnal_id', $j->id)->where('status_hadir', 'Sakit')->count();
            $j->izin = DB::table('tbl_ekskul_absen')->where('jurnal_id', $j->id)->where('status_hadir', 'Izin')->count();
            $j->alpa = DB::table('tbl_ekskul_absen')->where('jurnal_id', $j->id)->where('status_hadir', 'Alpa')->count();
        }

        return Inertia::render('Guru/Ekskul/Jurnal', [
            'ekskul' => $ekskul,
            'jurnals' => $jurnals
        ]);
    }

    public function jurnalSimpan(Request $request)
    {
        $request->validate([
            'ekskul_id' => 'required',
            'tanggal' => 'required|date',
            'materi_kegiatan' => 'required|string',
            'foto_1' => 'nullable|image',
            'foto_2' => 'nullable|image'
        ]);

        $guru_id = $this->getGuruId();
        $namaFoto1 = null;
        $namaFoto2 = null;
        $dir = public_path('uploads/ekskul/jurnal');
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        if ($request->hasFile('foto_1')) {
            $namaFoto1 = $request->file('foto_1')->hashName();
            $request->file('foto_1')->move($dir, $namaFoto1);
        }
        if ($request->hasFile('foto_2')) {
            $namaFoto2 = $request->file('foto_2')->hashName();
            $request->file('foto_2')->move($dir, $namaFoto2);
        }

        $jurnal_id = DB::table('tbl_ekskul_jurnal')->insertGetId([
            'ekskul_id' => $request->ekskul_id,
            'pembina_id' => $guru_id,
            'tanggal' => $request->tanggal,
            'materi_kegiatan' => $request->materi_kegiatan,
            'foto_1' => $namaFoto1,
            'foto_2' => $namaFoto2
        ]);

        // Otomatis generate absensi ALPA untuk semua anggota aktif
        $anggota = DB::table('tbl_ekskul_anggota')->where('ekskul_id', $request->ekskul_id)->where('status_anggota', 'Approved')->get();
        $absenData = [];
        foreach($anggota as $a) {
            $absenData[] = [
                'jurnal_id' => $jurnal_id,
                'siswa_id' => $a->siswa_id,
                'status_hadir' => 'Alpa'
            ];
        }
        if(count($absenData) > 0) {
            DB::table('tbl_ekskul_absen')->insert($absenData);
        }

        return back()->with('message', 'Jurnal berhasil disimpan dan draft absensi telah dibuat!');
    }

    public function absenScan($id)
    {
        // $id is Jurnal ID
        $jurnal = DB::table('tbl_ekskul_jurnal')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_jurnal.ekskul_id')
            ->where('tbl_ekskul_jurnal.id', $id)
            ->select('tbl_ekskul_jurnal.*', 'tbl_ekskul.nama_ekskul')
            ->first();
        if(!$jurnal) abort(404);

        $absensi = DB::table('tbl_ekskul_absen')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_absen.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_absen.jurnal_id', $id)
            ->select('tbl_ekskul_absen.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_siswa.nisn', 'tbl_kelas.nama_kelas', 'tbl_siswa.foto')
            ->orderBy('tbl_ekskul_absen.waktu_scan', 'desc')
            ->get();

        return Inertia::render('Guru/Ekskul/Scanner', [
            'jurnal' => $jurnal,
            'absensi' => $absensi
        ]);
    }

    public function prosesScan(Request $request)
    {
        $jurnal_id = $request->jurnal_id;
        $nis = $request->nis; // barcode content

        // Cari siswa
        $siswa = DB::table('tbl_siswa')->where('nis', $nis)->orWhere('nisn', $nis)->first();
        if(!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data Siswa tidak ditemukan di sistem!']);
        }

        // Cek apakah siswa anggota ekskul ini pada jurnal ini
        $absen = DB::table('tbl_ekskul_absen')
            ->where('jurnal_id', $jurnal_id)
            ->where('siswa_id', $siswa->id)
            ->first();

        if(!$absen) {
            return response()->json(['success' => false, 'message' => 'Siswa bukan anggota aktif Ekskul ini!']);
        }

        if($absen->status_hadir === 'Hadir') {
            return response()->json(['success' => false, 'message' => "Siswa {$siswa->nama_lengkap} sudah melakukan presensi sebelumnya!"]);
        }

        // Update Hadir
        DB::table('tbl_ekskul_absen')->where('id', $absen->id)->update([
            'status_hadir' => 'Hadir',
            'waktu_scan' => date('H:i:s')
        ]);

        return response()->json([
            'success' => true, 
            'message' => "Berhasil! {$siswa->nama_lengkap} telah Hadir.",
            'siswa' => $siswa
        ]);
    }

    public function absenManual(Request $request)
    {
        $absen_id = $request->id;
        $status = $request->status; // Hadir, Sakit, Izin, Alpa
        
        DB::table('tbl_ekskul_absen')->where('id', $absen_id)->update([
            'status_hadir' => $status,
            'waktu_scan' => $status === 'Hadir' ? date('H:i:s') : null
        ]);

        return back()->with('message', 'Status kehadiran berhasil diperbarui!');
    }

    public function prestasi($id)
    {
        $ekskul = DB::table('tbl_ekskul')->where('id', $id)->first();
        if(!$ekskul) abort(404);

        $prestasi = DB::table('tbl_ekskul_prestasi')
            ->where('ekskul_id', $id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return Inertia::render('Guru/Ekskul/Prestasi', [
            'ekskul' => $ekskul,
            'prestasi' => $prestasi
        ]);
    }

    public function prestasiSimpan(Request $request)
    {
        $request->validate([
            'ekskul_id' => 'required',
            'nama_lomba' => 'required|string',
            'tingkat' => 'required|string',
            'juara' => 'required|string',
            'tanggal' => 'required|date',
            'foto_dokumentasi' => 'required|image'
        ]);

        $namaFoto = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $dir = public_path('uploads/ekskul/prestasi');
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            $namaFoto = $request->file('foto_dokumentasi')->hashName();
            $request->file('foto_dokumentasi')->move($dir, $namaFoto);
        }

        DB::table('tbl_ekskul_prestasi')->insert([
            'ekskul_id' => $request->ekskul_id,
            'nama_lomba' => $request->nama_lomba,
            'tingkat' => $request->tingkat,
            'juara' => $request->juara,
            'tanggal' => $request->tanggal,
            'deskripsi_caption' => $request->deskripsi_caption,
            'foto_dokumentasi' => $namaFoto
        ]);

        return back()->with('message', 'Prestasi berhasil ditambahkan!');
    }

    public function penilaian($id)
    {
        $ekskul = DB::table('tbl_ekskul')->where('id', $id)->first();
        if(!$ekskul) abort(404);

        // Update percentase kehadiran otomatis
        $anggota = DB::table('tbl_ekskul_anggota')->where('ekskul_id', $id)->where('status_anggota', 'Approved')->get();
        $totalJurnal = DB::table('tbl_ekskul_jurnal')->where('ekskul_id', $id)->count();

        foreach($anggota as $a) {
            $hadir = DB::table('tbl_ekskul_absen')
                ->join('tbl_ekskul_jurnal', 'tbl_ekskul_jurnal.id', '=', 'tbl_ekskul_absen.jurnal_id')
                ->where('tbl_ekskul_jurnal.ekskul_id', $id)
                ->where('tbl_ekskul_absen.siswa_id', $a->siswa_id)
                ->where('tbl_ekskul_absen.status_hadir', 'Hadir')
                ->count();
            
            $persen = $totalJurnal > 0 ? round(($hadir / $totalJurnal) * 100) : 0;
            
            // Cek apakah data nilai ada
            $cekNilai = DB::table('tbl_ekskul_nilai')
                ->where('ekskul_id', $id)
                ->where('siswa_id', $a->siswa_id)
                ->where('semester', 'Ganjil 2026/2027') // TODO: ambil dari settings
                ->first();

            if($cekNilai) {
                DB::table('tbl_ekskul_nilai')->where('id', $cekNilai->id)->update(['persen_hadir' => $persen]);
            } else {
                DB::table('tbl_ekskul_nilai')->insert([
                    'ekskul_id' => $id,
                    'siswa_id' => $a->siswa_id,
                    'semester' => 'Ganjil 2026/2027',
                    'nilai_huruf' => 'B',
                    'deskripsi_dapodik' => "Baik, Mengikuti program ekstrakurikuler {$ekskul->nama_ekskul} dengan baik.",
                    'persen_hadir' => $persen,
                    'layak_sertifikat' => $persen >= 75 ? 'Y' : 'N'
                ]);
            }
        }

        $penilaian = DB::table('tbl_ekskul_nilai')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_nilai.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_nilai.ekskul_id', $id)
            ->where('tbl_ekskul_nilai.semester', 'Ganjil 2026/2027')
            ->select('tbl_ekskul_nilai.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_kelas.nama_kelas')
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')
            ->get();

        return Inertia::render('Guru/Ekskul/Penilaian', [
            'ekskul' => $ekskul,
            'penilaian' => $penilaian,
            'total_pertemuan' => $totalJurnal
        ]);
    }

    public function penilaianSimpan(Request $request)
    {
        $id = $request->id;
        DB::table('tbl_ekskul_nilai')->where('id', $id)->update([
            'nilai_huruf' => $request->nilai_huruf,
            'deskripsi_dapodik' => $request->deskripsi_dapodik,
            'layak_sertifikat' => $request->layak_sertifikat
        ]);

        return back()->with('message', 'Nilai berhasil disimpan!');
    }

    public function cetakSertifikat($id)
    {
        $nilai = DB::table('tbl_ekskul_nilai')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_nilai.siswa_id')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_nilai.ekskul_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_nilai.id', $id)
            ->select('tbl_ekskul_nilai.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nisn', 'tbl_siswa.nis', 'tbl_kelas.nama_kelas', 'tbl_ekskul.nama_ekskul')
            ->first();

        if(!$nilai || $nilai->layak_sertifikat !== 'Y') {
            abort(403, 'Sertifikat tidak tersedia atau siswa tidak layak.');
        }

        // Generate Token if empty
        if(empty($nilai->token_sertifikat)) {
            $token = 'SRT-EKS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
            DB::table('tbl_ekskul_nilai')->where('id', $id)->update(['token_sertifikat' => $token]);
            $nilai->token_sertifikat = $token;
        }

        // Dapatkan data Kepala Sekolah & Wakasek Kesiswaan (contoh fallback jika tidak ada)
        $sekolah = DB::table('tbl_sekolah')->first();
        $kepsek = DB::table('tbl_guru')->where('id', $sekolah->kepala_sekolah_id ?? 0)->first();
        $guru_id = $this->getGuruId();
        $pembina = DB::table('tbl_guru')->where('id', $guru_id)->first();

        $data = [
            'nilai' => $nilai,
            'sekolah' => $sekolah,
            'kepsek' => $kepsek,
            'pembina' => $pembina,
            'tanggal_cetak' => Carbon::now()->isoFormat('D MMMM Y')
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sertifikat_ekskul', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream("Sertifikat_Ekskul_{$nilai->nama_lengkap}.pdf");
    }
}
