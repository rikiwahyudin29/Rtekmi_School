<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class EkstrakurikulerController extends Controller
{
    // ==========================================
    // 1. DASHBOARD GURU PEMBINA
    // ==========================================
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? null;
        if (!$guru_id) {
            return redirect()->back()->with('error', 'Akses ditolak. Profil guru tidak ditemukan.');
        }

        // Cari ekskul apa saja yang dibina oleh Guru ini
        $ekskul_binaan = DB::table('tbl_ekskul_pembina')
            ->select('tbl_ekskul.*', 'tbl_ekskul_pembina.id as pembina_id')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_pembina.ekskul_id')
            ->where('tbl_ekskul_pembina.guru_id', $guru_id)
            ->where('tbl_ekskul_pembina.status', 'Aktif')
            ->get();

        $data_binaan = [];
        $total_pending = 0;

        foreach ($ekskul_binaan as $eb) {
            // Hitung jumlah anggota aktif (Approved)
            $jml_aktif = DB::table('tbl_ekskul_anggota')
                ->where('ekskul_id', $eb->id)
                ->where('status_anggota', 'Approved')
                ->count();
            
            // Hitung pendaftar baru yang menunggu validasi (Pending)
            $jml_pending = DB::table('tbl_ekskul_anggota')
                ->where('ekskul_id', $eb->id)
                ->where('status_anggota', 'Pending')
                ->count();
            
            $total_pending += $jml_pending;

            $eb->jml_aktif = $jml_aktif;
            $eb->jml_pending = $jml_pending;
            $data_binaan[] = $eb;
        }

        return Inertia::render('Guru/Ekskul/Index', [
            'binaan' => $data_binaan,
            'total_pending' => $total_pending
        ]);
    }

    // ==========================================
    // 2. KELOLA ANGGOTA & VALIDASI PENDAFTARAN
    // ==========================================
    public function anggota($ekskul_id)
    {
        $guru_id = Auth::user()->guru->id ?? null;

        // Validasi keamanan: Pastikan guru ini benar-benar pembina ekskul tersebut
        $cek_pembina = DB::table('tbl_ekskul_pembina')
            ->where('guru_id', $guru_id)
            ->where('ekskul_id', $ekskul_id)
            ->where('status', 'Aktif')
            ->count();
        
        if($cek_pembina == 0) {
            return redirect()->route('guru.ekskul.index')->with('error', 'Akses ilegal! Anda bukan pembina di unit ekskul ini.');
        }

        $ekskul = DB::table('tbl_ekskul')->where('id', $ekskul_id)->first();

        // Ambil Data Pendaftar Baru (Pending)
        $pendaftar = DB::table('tbl_ekskul_anggota')
            ->select('tbl_ekskul_anggota.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_kelas.nama_kelas', 'tbl_siswa.jenis_kelamin as jk')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_anggota.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_anggota.ekskul_id', $ekskul_id)
            ->where('tbl_ekskul_anggota.status_anggota', 'Pending')
            ->orderBy('tbl_ekskul_anggota.tgl_daftar', 'asc')
            ->get();

        // Ambil Data Anggota Aktif (Approved)
        $anggota = DB::table('tbl_ekskul_anggota')
            ->select('tbl_ekskul_anggota.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_kelas.nama_kelas', 'tbl_siswa.jenis_kelamin as jk')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_anggota.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_anggota.ekskul_id', $ekskul_id)
            ->where('tbl_ekskul_anggota.status_anggota', 'Approved')
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')
            ->get();

        return Inertia::render('Guru/Ekskul/Anggota', [
            'ekskul' => $ekskul,
            'pendaftar' => $pendaftar,
            'anggota' => $anggota
        ]);
    }

    public function validasiAnggota(Request $request, $id)
    {
        $status = $request->input('status'); // 'Approved' or 'Rejected'
        
        $anggota = DB::table('tbl_ekskul_anggota')->where('id', $id)->first();
        if(!$anggota) return redirect()->route('guru.ekskul.index');

        DB::table('tbl_ekskul_anggota')->where('id', $id)->update(['status_anggota' => $status]);
        
        $pesan = $status == 'Approved' ? 'Siswa berhasil diterima menjadi anggota!' : 'Pendaftaran siswa ditolak/dikeluarkan.';
        
        return redirect()->route('guru.ekskul.anggota', $anggota->ekskul_id)->with('message', $pesan);
    }

    // ==========================================
    // 3. JURNAL KEGIATAN & SMART ATTENDANCE
    // ==========================================
    public function jurnal($ekskul_id)
    {
        $guru_id = Auth::user()->guru->id;
        $ekskul = DB::table('tbl_ekskul')->where('id', $ekskul_id)->first();

        // Ambil daftar jurnal/kegiatan yang sudah dibuat oleh pembina ini
        $jurnal = DB::table('tbl_ekskul_jurnal')
            ->where('ekskul_id', $ekskul_id)
            ->where('pembina_id', $guru_id)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung statistik absen tiap jurnal
        foreach($jurnal as $j) {
            $j->hadir = DB::table('tbl_ekskul_absen')
                ->where('jurnal_id', $j->id)
                ->where('status_hadir', 'Hadir')
                ->count();
            $j->total_anggota = DB::table('tbl_ekskul_absen')
                ->where('jurnal_id', $j->id)
                ->count();
        }

        return Inertia::render('Guru/Ekskul/Jurnal', [
            'ekskul' => $ekskul,
            'jurnal' => $jurnal
        ]);
    }

    public function jurnalSimpan(Request $request)
    {
        $guru_id = Auth::user()->guru->id;
        $ekskul_id = $request->input('ekskul_id');
        
        $request->validate([
            'tanggal' => 'required|date',
            'materi_kegiatan' => 'required|string',
            'foto_1' => 'nullable|image|max:2048'
        ]);

        $namaFoto1 = null;
        if ($request->hasFile('foto_1') && $request->file('foto_1')->isValid()) {
            $dir = public_path('uploads/ekskul_jurnal');
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            $file = $request->file('foto_1');
            $namaFoto1 = $file->hashName();
            $file->move($dir, $namaFoto1);
        }

        // 1. Simpan Jurnal
        $jurnal_id = DB::table('tbl_ekskul_jurnal')->insertGetId([
            'ekskul_id' => $ekskul_id,
            'pembina_id' => $guru_id,
            'tanggal' => $request->input('tanggal'),
            'materi_kegiatan' => $request->input('materi_kegiatan'),
            'foto_1' => $namaFoto1
        ]);

        // 2. Generate Daftar Absen (Otomatis ALPA semua di awal)
        $anggota = DB::table('tbl_ekskul_anggota')
            ->where('ekskul_id', $ekskul_id)
            ->where('status_anggota', 'Approved')
            ->get();

        if($anggota->count() > 0) {
            $data_absen = [];
            foreach($anggota as $a) {
                $data_absen[] = [
                    'jurnal_id' => $jurnal_id,
                    'siswa_id' => $a->siswa_id,
                    'status_hadir' => 'Alpa'
                ];
            }
            DB::table('tbl_ekskul_absen')->insert($data_absen);
        }

        return redirect()->route('guru.ekskul.jurnal', $ekskul_id)->with('message', 'Jurnal berhasil dibuat! Silakan mulai sesi Presensi (Scan QR/Barcode).');
    }

    public function absenScan($jurnal_id)
    {
        $jurnal = DB::table('tbl_ekskul_jurnal')
            ->select('tbl_ekskul_jurnal.*', 'tbl_ekskul.nama_ekskul')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_jurnal.ekskul_id')
            ->where('tbl_ekskul_jurnal.id', $jurnal_id)
            ->first();
        
        if(!$jurnal) return redirect()->route('guru.ekskul.index')->with('error', 'Data jurnal tidak ditemukan.');

        $absen = DB::table('tbl_ekskul_absen')
            ->select('tbl_ekskul_absen.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_kelas.nama_kelas')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_absen.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_absen.jurnal_id', $jurnal_id)
            ->orderBy('tbl_ekskul_absen.status_hadir', 'desc') 
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')
            ->get();

        return Inertia::render('Guru/Ekskul/AbsenScan', [
            'jurnal' => $jurnal,
            'absen' => $absen
        ]);
    }

    public function prosesScan(Request $request)
    {
        $nis = $request->input('nis');
        $jurnal_id = $request->input('jurnal_id');

        $siswa = DB::table('tbl_siswa')->where('nis', $nis)->first();
        if(!$siswa) return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);

        $absen = DB::table('tbl_ekskul_absen')->where('jurnal_id', $jurnal_id)->where('siswa_id', $siswa->id)->first();
        if(!$absen) return response()->json(['status' => 'error', 'message' => 'Siswa bukan anggota ekskul ini!']);

        if($absen->status_hadir == 'Hadir') {
            return response()->json(['status' => 'warning', 'message' => $siswa->nama_lengkap . ' sudah absen sebelumnya.']);
        }

        DB::table('tbl_ekskul_absen')->where('id', $absen->id)->update([
            'status_hadir' => 'Hadir',
            'waktu_scan' => now()->format('H:i:s')
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'BEEP! ' . $siswa->nama_lengkap . ' Hadir.',
            'waktu' => now()->format('H:i:s')
        ]);
    }

    public function absenManual(Request $request)
    {
        $id_absen = $request->input('id_absen');
        $status = $request->input('status_hadir');
        
        DB::table('tbl_ekskul_absen')->where('id', $id_absen)->update(['status_hadir' => $status]);
        
        $absen_data = DB::table('tbl_ekskul_absen')->where('id', $id_absen)->first();
        if($absen_data) {
            return back()->with('message', 'Status absen manual berhasil diubah.');
        }
        
        return back();
    }

    // ==========================================
    // 4. GALERI PRESTASI & EVENT
    // ==========================================
    public function prestasi($ekskul_id)
    {
        $ekskul = DB::table('tbl_ekskul')->where('id', $ekskul_id)->first();
        $prestasi = DB::table('tbl_ekskul_prestasi')
            ->where('ekskul_id', $ekskul_id)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        return Inertia::render('Guru/Ekskul/Prestasi', [
            'ekskul' => $ekskul,
            'prestasi' => $prestasi
        ]);
    }

    public function prestasiSimpan(Request $request)
    {
        $ekskul_id = $request->input('ekskul_id');
        $request->validate([
            'nama_lomba' => 'required',
            'tingkat' => 'required',
            'juara' => 'required',
            'tanggal' => 'required|date',
            'foto_dokumentasi' => 'nullable|image|max:2048'
        ]);

        $namaFoto = 'default_prestasi.png';
        if ($request->hasFile('foto_dokumentasi') && $request->file('foto_dokumentasi')->isValid()) {
            $dir = public_path('uploads/ekskul_prestasi');
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            $file = $request->file('foto_dokumentasi');
            $namaFoto = $file->hashName();
            $file->move($dir, $namaFoto);
        }

        DB::table('tbl_ekskul_prestasi')->insert([
            'ekskul_id' => $ekskul_id,
            'nama_lomba' => $request->input('nama_lomba'),
            'tingkat' => $request->input('tingkat'),
            'juara' => $request->input('juara'),
            'tanggal' => $request->input('tanggal'),
            'deskripsi_caption' => $request->input('deskripsi_caption'),
            'foto_dokumentasi' => $namaFoto
        ]);

        return back()->with('message', 'Prestasi berhasil dipublikasikan!');
    }

    // ==========================================
    // 5. PENILAIAN DAPODIK & PENERBITAN SERTIFIKAT
    // ==========================================
    public function penilaian($ekskul_id)
    {
        $ekskul = DB::table('tbl_ekskul')->where('id', $ekskul_id)->first();
        
        // Ambil Anggota Aktif
        $anggota = DB::table('tbl_ekskul_anggota')
            ->select('tbl_ekskul_anggota.*', 'tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_kelas.nama_kelas')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_anggota.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->where('tbl_ekskul_anggota.ekskul_id', $ekskul_id)
            ->where('tbl_ekskul_anggota.status_anggota', 'Approved')
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')
            ->get();

        // Hitung Kehadiran & Cek Nilai yang sudah ada
        $total_pertemuan = DB::table('tbl_ekskul_jurnal')->where('ekskul_id', $ekskul_id)->count();
        
        foreach($anggota as $a) {
            $hadir = DB::table('tbl_ekskul_absen')
                ->join('tbl_ekskul_jurnal', 'tbl_ekskul_jurnal.id', '=', 'tbl_ekskul_absen.jurnal_id')
                ->where('tbl_ekskul_jurnal.ekskul_id', $ekskul_id)
                ->where('tbl_ekskul_absen.siswa_id', $a->siswa_id)
                ->where('tbl_ekskul_absen.status_hadir', 'Hadir')
                ->count();
            
            $a->persen_hadir = $total_pertemuan > 0 ? round(($hadir / $total_pertemuan) * 100) : 0;
            
            // Cek apakah sudah dinilai
            $a->nilai = DB::table('tbl_ekskul_nilai')
                ->where('ekskul_id', $ekskul_id)
                ->where('siswa_id', $a->siswa_id)
                ->orderBy('id', 'desc')
                ->first();
        }

        return Inertia::render('Guru/Ekskul/Penilaian', [
            'ekskul' => $ekskul,
            'anggota' => $anggota
        ]);
    }

    public function penilaianSimpan(Request $request)
    {
        $ekskul_id = $request->input('ekskul_id');
        $semester = $request->input('semester');
        $nilai_data = $request->input('nilai_data'); // array of {siswa_id, nilai_huruf, persen_hadir}

        $nama_ekskul = DB::table('tbl_ekskul')->where('id', $ekskul_id)->value('nama_ekskul');

        foreach ($nilai_data as $data) {
            $siswa_id = $data['siswa_id'];
            $nilai = $data['nilai_huruf'];
            $hadir = $data['persen_hadir'];

            if(!empty($nilai)) {
                // 1. Generator Deskripsi Dapodik
                $deskripsi = "";
                if($nilai == 'A' || $nilai == 'Sangat Baik') $deskripsi = "Sangat Baik. Siswa sangat aktif, disiplin, dan menunjukkan jiwa kepemimpinan yang luar biasa dalam kegiatan " . $nama_ekskul . ".";
                elseif($nilai == 'B' || $nilai == 'Baik') $deskripsi = "Baik. Siswa aktif, disiplin, dan mampu mengikuti seluruh program kegiatan " . $nama_ekskul . " dengan baik.";
                elseif($nilai == 'C' || $nilai == 'Cukup') $deskripsi = "Cukup. Siswa cukup aktif mengikuti kegiatan " . $nama_ekskul . ", namun perlu peningkatan dalam kedisiplinan.";
                else $deskripsi = "Kurang. Siswa jarang hadir dan kurang proaktif dalam kegiatan " . $nama_ekskul . ".";

                // 2. Logika Kelayakan (Nilai Min B & Hadir >= 80%)
                $layak = (in_array($nilai, ['A', 'B', 'Sangat Baik', 'Baik']) && $hadir >= 80) ? 'Y' : 'N';
                $token = $layak == 'Y' ? md5(uniqid(rand(), true)) : null;

                // 3. Simpan Nilai
                $cek = DB::table('tbl_ekskul_nilai')
                    ->where('ekskul_id', $ekskul_id)->where('siswa_id', $siswa_id)->where('semester', $semester)
                    ->first();

                $data_simpan = [
                    'ekskul_id' => $ekskul_id,
                    'siswa_id' => $siswa_id,
                    'semester' => $semester,
                    'nilai_huruf' => $nilai,
                    'deskripsi_dapodik' => $deskripsi,
                    'persen_hadir' => $hadir,
                    'layak_sertifikat' => $layak,
                    'token_sertifikat' => $token
                ];

                if($cek) {
                    DB::table('tbl_ekskul_nilai')->where('id', $cek->id)->update($data_simpan);
                    $nilai_id = $cek->id;
                } else {
                    $nilai_id = DB::table('tbl_ekskul_nilai')->insertGetId($data_simpan);
                }

                // 4. INJECT OTOMATIS KE E-ARSIP (SURAT KELUAR)
                if($layak == 'Y') {
                    $singkatan = strtoupper(substr(str_replace(' ', '', $nama_ekskul), 0, 3));
                    $nomor_surat = sprintf("%03d", $nilai_id) . "/EKS-" . $singkatan . "/" . date('m/Y');
                    
                    $cek_surat = DB::table('tbl_surat_keluar')->where('token_validasi', $token)->count();
                    if($cek_surat == 0) {
                        DB::table('tbl_surat_keluar')->insert([
                            'no_surat' => $nomor_surat,
                            'siswa_id' => $siswa_id,
                            'perihal' => 'Sertifikat Ekstrakurikuler ' . $nama_ekskul,
                            'tgl_surat' => date('Y-m-d'),
                            'token_validasi' => $token,
                            'status' => 'Disetujui'
                        ]);
                    }
                }
            }
        }

        return back()->with('message', 'Penilaian Rapor dan E-Sertifikat berhasil diproses! Sertifikat yang memenuhi syarat sudah masuk ke E-Arsip.');
    }

    // ==========================================
    // 6. CETAK E-SERTIFIKAT EKSKUL (DOMPDF)
    // ==========================================
    public function cetakSertifikat($ekskul_id)
    {
        $id_user = Auth::id();
        $siswa = DB::table('tbl_siswa')->where('user_id', $id_user)->first();
        
        if (!$siswa) {
            return back()->with('error', 'Hanya siswa yang dapat mencetak sertifikat.');
        }

        // Cek Nilai dan Kelayakan Sertifikat
        $nilai = DB::table('tbl_ekskul_nilai')
            ->select('tbl_ekskul_nilai.*', 'tbl_ekskul.nama_ekskul', 'tbl_ekskul.logo')
            ->join('tbl_ekskul', 'tbl_ekskul.id', '=', 'tbl_ekskul_nilai.ekskul_id')
            ->where('tbl_ekskul_nilai.siswa_id', $siswa->id)
            ->where('tbl_ekskul_nilai.ekskul_id', $ekskul_id)
            ->where('tbl_ekskul_nilai.layak_sertifikat', 'Y')
            ->orderBy('id', 'desc')->first();

        if(!$nilai) return back()->with('error', 'Sertifikat belum tersedia atau Anda belum memenuhi kualifikasi.');

        $data['sekolah'] = DB::table('tbl_sekolah')->where('id', 1)->first();
        $data['siswa'] = $siswa;
        $data['nilai'] = $nilai;

        $pdf = Pdf::loadView('cetak.sertifikat_ekskul', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('Sertifikat_Ekskul_' . $siswa->nama_lengkap . '.pdf');
    }

    // ==========================================
    // 7. CETAK LAPORAN PERTANGGUNGJAWABAN (LPJ)
    // ==========================================
    public function cetakLpj($ekskul_id)
    {
        $guru = Auth::user()->guru;
        if (!$guru) return back()->with('error', 'Akses ditolak.');
        
        $ekskul = DB::table('tbl_ekskul')->where('id', $ekskul_id)->first();
        $sekolah = DB::table('tbl_sekolah')->where('id', 1)->first();

        // Data Jurnal & Kehadiran
        $jurnal = DB::table('tbl_ekskul_jurnal')
            ->where('ekskul_id', $ekskul_id)
            ->where('pembina_id', $guru->id)
            ->orderBy('tanggal', 'asc')->get();
            
        foreach($jurnal as $j) {
            $j->hadir = DB::table('tbl_ekskul_absen')->where('jurnal_id', $j->id)->where('status_hadir', 'Hadir')->count();
            $j->total = DB::table('tbl_ekskul_absen')->where('jurnal_id', $j->id)->count();
        }

        // Data Prestasi
        $prestasi = DB::table('tbl_ekskul_prestasi')
            ->where('ekskul_id', $ekskul_id)->orderBy('tanggal', 'asc')->get();

        // Data Anggota & Nilai Akhir
        $anggota = DB::table('tbl_ekskul_anggota')
            ->select('tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_kelas.nama_kelas', 'tbl_ekskul_nilai.nilai_huruf', 'tbl_ekskul_nilai.persen_hadir')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_ekskul_anggota.siswa_id')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->leftJoin('tbl_ekskul_nilai', function($join) use ($ekskul_id) {
                $join->on('tbl_ekskul_nilai.siswa_id', '=', 'tbl_ekskul_anggota.siswa_id')
                     ->where('tbl_ekskul_nilai.ekskul_id', '=', $ekskul_id);
            })
            ->where('tbl_ekskul_anggota.ekskul_id', $ekskul_id)
            ->where('tbl_ekskul_anggota.status_anggota', 'Approved')
            ->orderBy('tbl_siswa.nama_lengkap', 'asc')->get();

        $data = [
            'ekskul' => $ekskul, 'sekolah' => $sekolah, 'pembina' => $guru,
            'jurnal' => $jurnal, 'prestasi' => $prestasi, 'anggota' => $anggota
        ];

        $pdf = Pdf::loadView('cetak.lpj_ekskul', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('LPJ_Ekskul_' . $ekskul->nama_ekskul . '.pdf');
    }
}
