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
        return DB::table('tbl_siswa as s')
            ->select('s.id', 's.nama_lengkap', 's.kelas_id', 's.nisn', 's.foto', 'k.nama_kelas')
            ->leftJoin('tbl_kelas as k', 'k.id', '=', 's.kelas_id')
            ->where('s.nisn', $nisn)
            ->first();
    }

    // =======================================================
    // 1. API RINGKASAN DASHBOARD
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
            ->where('deadline', '>=', date('Y-m-d H:i:s'))
            ->count();

        // D. Data Ujian Berlangsung/Hari ini
        $ujian_raw = DB::table('ujian_siswa as us')
            ->select(
                'ju.id as id_ujian',
                'ju.nama_ujian as nama_mapel',
                'ju.waktu_mulai',
                'ju.waktu_selesai'
            )
            ->join('tbl_jadwal_ujian as ju', 'ju.id', '=', 'us.jadwal_id')
            ->where('us.siswa_id', $siswa->id)
            ->where('ju.status', 1)
            ->whereDate('ju.waktu_mulai', date('Y-m-d'))
            ->orderBy('ju.waktu_mulai', 'asc')
            ->get();

        $ujian_hari_ini = [];
        foreach ($ujian_raw as $u) {
            $waktu_mulai = date('H:i', strtotime($u->waktu_mulai));
            $waktu_selesai = date('H:i', strtotime($u->waktu_selesai));
            
            $ujian_hari_ini[] = [
                'id_ujian' => $u->id_ujian,
                'nama_mapel' => $u->nama_mapel,
                'waktu' => $waktu_mulai . ' - ' . $waktu_selesai,
                'ruang' => 'Ruang Ujian'
            ];
        }

        // E. Jadwal Pelajaran Hari Ini
        $map_hari = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
        $hari_ini = $map_hari[date('N')];

        $jadwal_raw = DB::table('tbl_jadwal as j')
            ->select('m.nama_mapel', 'j.jam_mulai', 'j.jam_selesai', 'g.nama_lengkap as guru')
            ->leftJoin('tbl_mapel as m', 'm.id', '=', 'j.id_mapel')
            ->leftJoin('tbl_guru as g', 'g.id', '=', 'j.id_guru')
            ->where('j.id_kelas', $siswa->kelas_id)
            ->where('j.hari', $hari_ini)
            ->orderBy('j.jam_mulai', 'asc')
            ->get();

        $jadwal_hari_ini = [];
        foreach ($jadwal_raw as $j) {
            $waktu_mulai = date('H:i', strtotime($j->jam_mulai));
            $waktu_selesai = date('H:i', strtotime($j->jam_selesai));
            $jadwal_hari_ini[] = [
                'nama_mapel' => $j->nama_mapel ?? 'Mapel Tidak Diketahui',
                'waktu' => $waktu_mulai . ' - ' . $waktu_selesai,
                'guru' => $j->guru ?? 'Guru Belum Ditentukan',
                'ruang' => $siswa->nama_kelas ?? 'Ruang Kelas'
            ];
        }

        // F. Format Foto Profil URL
        $foto_profil = null;
        if (!empty($siswa->foto) && $siswa->foto !== 'default.png') {
            $foto_profil = url('uploads/siswa/' . $siswa->foto);
        }

        // G. (Baru) Data Tambahan untuk UI Android Baru
        $tahun_ajaran_aktif = DB::table('tbl_tahun_ajaran')->where('status', 'Aktif')->first();
        $semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;
        $tahun_ajaran_id = $tahun_ajaran_aktif ? $tahun_ajaran_aktif->id : 1;

        $rata_rata_nilai = DB::table('tbl_rapor_akhir')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->where('semester', $semester_int)
            ->avg('nilai_akhir') ?? 0;

        $pesan_baru = 0;

        $aktivitas_raw = DB::table('tbl_tugas_kumpul as tk')
            ->select('tt.judul as judul', 'tk.tgl_kumpul as waktu', 'tk.status_kumpul as status')
            ->join('tbl_tugas as tt', 'tt.id', '=', 'tk.tugas_id')
            ->where('tk.siswa_id', $siswa->id)
            ->orderBy('tk.tgl_kumpul', 'desc')
            ->limit(5)
            ->get();
            
        $aktivitas_terkini = [];
        foreach ($aktivitas_raw as $act) {
            $aktivitas_terkini[] = [
                'judul' => "Mengumpulkan " . $act->judul,
                'waktu' => date('d M Y, H:i', strtotime($act->waktu)) . ' WIB',
                'status' => 'Selesai',
                'icon' => 'task'
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Dashboard',
            'data' => [
                // Data Lama
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama_lengkap,
                'kelas' => $siswa->nama_kelas ?? 'Belum Ada Kelas',
                'tahun_ajaran' => $tahun_ajaran_aktif ? $tahun_ajaran_aktif->tahun_ajaran : 'Belum Ada',
                'semester' => $tahun_ajaran_aktif ? $tahun_ajaran_aktif->semester : 'Belum Ada',
                'foto_profil' => $foto_profil,
                'keuangan' => $total_tunggakan,
                'tugas_aktif' => $tugas_aktif,
                'poin_disiplin' => $poin_disiplin,
                'ujian_hari_ini' => $ujian_hari_ini,
                'jadwal_hari_ini' => $jadwal_hari_ini,
                // Data Baru
                'rata_rata_nilai' => round((float)$rata_rata_nilai, 1),
                'pesan_baru' => $pesan_baru,
                'aktivitas_terkini' => $aktivitas_terkini
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
        $kelas_id = $request->input('kelas') ?? ($siswa ? $siswa->kelas_id : null);

        if (!$kelas_id) {
            return response()->json(['status' => false, 'message' => 'Kelas tidak valid.'], 404);
        }

        // Ambil materi sesuai kelas siswa
        $materi_raw = DB::table('tbl_materi as tm')
            ->select('tm.id as id_materi', 'tm.file_materi', 'tm.link_youtube', 'tm.judul', 'tm.deskripsi', 'tm.created_at', 'mapel.nama_mapel')
            ->join('tbl_mapel as mapel', 'mapel.id', '=', 'tm.mapel_id')
            ->where('tm.kelas_id', $kelas_id)
            ->orderBy('mapel.nama_mapel', 'ASC')
            ->orderBy('tm.created_at', 'DESC')
            ->get();

        $grouped = [];
        foreach ($materi_raw as $m) {
            $ext = pathinfo($m->file_materi, PATHINFO_EXTENSION);
            
            $jenis_file = 'File';
            if ($m->link_youtube) {
                $jenis_file = 'Youtube';
            } elseif (in_array(strtolower($ext), ['pdf'])) {
                $jenis_file = 'PDF';
            } elseif (in_array(strtolower($ext), ['doc', 'docx'])) {
                $jenis_file = 'Word';
            } elseif (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $jenis_file = 'Foto';
            }

            $ukuran_file = '';
            if (!$m->link_youtube && $m->file_materi) {
                $filePath = public_path('uploads/materi/' . $m->file_materi);
                if (file_exists($filePath)) {
                    $bytes = filesize($filePath);
                    $ukuran_file = number_format($bytes / 1048576, 2) . ' MB';
                }
            }

            if (!isset($grouped[$m->nama_mapel])) {
                $grouped[$m->nama_mapel] = [];
            }
            $grouped[$m->nama_mapel][] = [
                'id_materi' => 'M' . $m->id_materi,
                'tipe' => strtoupper($ext) ?: 'FILE',
                'jenis_file' => $jenis_file,
                'judul' => $m->judul,
                'deskripsi' => $m->deskripsi, // Full deskripsi
                'link_youtube' => $m->link_youtube,
                'ukuran_file' => $ukuran_file,
                'ukuran' => $ukuran_file ?: '-', // Keep for backward compatibility
                'tanggal' => date('d M Y', strtotime($m->created_at))
            ];
        }

        $formatted_materi = [];
        foreach ($grouped as $mapel => $items) {
            $formatted_materi[] = [
                'mapel' => $mapel,
                'materi' => $items
            ];
        }

        return response()->json(['status' => true, 'data' => $formatted_materi], 200);
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
        $tugas_raw = DB::table('tbl_tugas as tt')
            ->select(
                'tt.id as id_tugas', 
                'tt.judul',
                'tt.deskripsi',
                'tt.file_pendukung',
                'tt.deadline',
                'mapel.nama_mapel', 
                'kumpul.id as id_kumpul', 
                'kumpul.nilai',
                'kumpul.komentar_guru',
                'kumpul.file_jawaban'
            )
            ->join('tbl_mapel as mapel', 'mapel.id', '=', 'tt.mapel_id')
            ->leftJoin('tbl_tugas_kumpul as kumpul', function ($join) use ($siswa) {
                $join->on('kumpul.tugas_id', '=', 'tt.id')
                     ->where('kumpul.siswa_id', '=', $siswa->id);
            })
            ->where('tt.kelas_id', $siswa->kelas_id)
            ->where('tt.status', 1)
            ->orderBy('tt.deadline', 'DESC')
            ->get();

        $berlangsung = [];
        $selesai = [];

        foreach ($tugas_raw as $t) {
            $formatted_tugas = [
                'id_tugas' => $t->id_tugas,
                'mapel' => $t->nama_mapel,
                'judul' => $t->judul,
                'deskripsi' => $t->deskripsi,
                'file_pendukung' => $t->file_pendukung ? url('uploads/tugas/' . $t->file_pendukung) : null,
                'deadline' => date('d M Y, H:i', strtotime($t->deadline)),
            ];

            if ($t->id_kumpul) {
                $formatted_tugas['status'] = $t->nilai !== null ? 'Dinilai' : 'Menunggu Nilai';
                $formatted_tugas['nilai'] = (string)$t->nilai; // API expects string according to user's example
                $formatted_tugas['komentar_guru'] = $t->komentar_guru;
                $formatted_tugas['file_jawaban'] = $t->file_jawaban ? url('uploads/tugas_siswa/' . $t->file_jawaban) : null;
                $selesai[] = $formatted_tugas;
            } else {
                $formatted_tugas['status'] = 'Belum Selesai';
                $berlangsung[] = $formatted_tugas;
            }
        }

        return response()->json([
            'status' => true, 
            'data' => [
                'berlangsung' => $berlangsung,
                'selesai' => $selesai
            ]
        ], 200);
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

    // =======================================================
    // 5. API RAPORT AKADEMIK
    // =======================================================
    public function getRaport(Request $request)
    {
        $nisn = $request->input('nisn');
        $semester_req = $request->input('semester'); // optional
        
        $siswa = $this->getSiswaInfo($nisn);
        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $tahun_ajaran_aktif = DB::table('tbl_tahun_ajaran')->where('status', 'Aktif')->first();
        if (!$tahun_ajaran_aktif) {
            return response()->json(['status' => false, 'message' => 'Tidak ada Tahun Ajaran aktif.'], 404);
        }

        $semester_int = ($tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;
        $tahun_ajaran_id = $tahun_ajaran_aktif->id;

        $semester_nama = "Semester " . $tahun_ajaran_aktif->semester . " " . $tahun_ajaran_aktif->tahun_ajaran;

        // Raport Akhir
        $rapor_akhir = DB::table('tbl_rapor_akhir as ra')
            ->select('ra.*', 'm.nama_mapel', 'g.nama_lengkap as guru')
            ->join('tbl_mapel as m', 'm.id', '=', 'ra.mapel_id')
            ->leftJoin('tbl_guru as g', 'g.id', '=', 'ra.guru_id')
            ->where('ra.siswa_id', $siswa->id)
            ->where('ra.tahun_ajaran_id', $tahun_ajaran_id)
            ->where('ra.semester', $semester_int)
            ->get();

        $rata_rata = $rapor_akhir->avg('nilai_akhir') ?? 0;

        // Peringkat & Total Siswa
        $siswa_kelas = DB::table('tbl_siswa')->where('kelas_id', $siswa->kelas_id)->pluck('id');
        $total_siswa = $siswa_kelas->count();
        
        // Hitung rata-rata per siswa di kelas ini untuk mencari peringkat
        $rata_kelas = DB::table('tbl_rapor_akhir')
            ->select('siswa_id', DB::raw('AVG(nilai_akhir) as rata'))
            ->whereIn('siswa_id', $siswa_kelas)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->where('semester', $semester_int)
            ->groupBy('siswa_id')
            ->orderBy('rata', 'DESC')
            ->pluck('rata', 'siswa_id')
            ->toArray();
            
        // Cari urutan array
        $peringkat = 1;
        $found = false;
        foreach ($rata_kelas as $s_id => $rata) {
            if ($s_id == $siswa->id) {
                $found = true;
                break;
            }
            $peringkat++;
        }
        if (!$found) $peringkat = '-';

        // Kehadiran
        $kehadiran = DB::table('tbl_rapor_kehadiran')
            ->where('siswa_id', $siswa->id)
            ->where('semester', $semester_int)
            ->first();
        
        $persen_hadir = 100;
        if ($kehadiran) {
            $total_absen = ($kehadiran->sakit ?? 0) + ($kehadiran->izin ?? 0) + ($kehadiran->tanpa_keterangan ?? 0);
            $persen_hadir = max(0, 100 - $total_absen); // Estimasi kasar jika tidak ada total hari
        }

        // Ambil Data Formatif & Sumatif
        $formatif_raw = DB::table('tbl_nilai_formatif as nf')
            ->select('nf.mapel_id', 'nf.nilai', 'tp.kode_tp', 'tp.mapel_id as fallback_mapel_id')
            ->join('tbl_tujuan_pembelajaran as tp', 'tp.id', '=', 'nf.tp_id')
            ->where('nf.siswa_id', $siswa->id)
            ->where(function($q) use ($tahun_ajaran_id) {
                $q->where('nf.tahun_ajaran_id', $tahun_ajaran_id)
                  ->orWhereNull('nf.tahun_ajaran_id');
            })
            ->get();
            
        $sumatif_raw = DB::table('tbl_nilai_sumatif')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->where('semester', $semester_int)
            ->get();

        $nilai_mapel = [];
        foreach ($rapor_akhir as $ra) {
            $mapel_id = $ra->mapel_id;
            
            // Filter formatif
            $f_mapel = [];
            foreach ($formatif_raw as $f) {
                $m_id = $f->mapel_id ?: $f->fallback_mapel_id;
                if ($m_id == $mapel_id) {
                    $f_mapel[] = [
                        'nama' => $f->kode_tp,
                        'nilai' => $f->nilai
                    ];
                }
            }

            // Filter sumatif
            $s_mapel = $sumatif_raw->where('mapel_id', $mapel_id);
            $sas = $s_mapel->where('jenis', 'SAS')->first()->nilai ?? 0;
            $sts = $s_mapel->where('jenis', 'STS')->first()->nilai ?? 0;

            $nilai_mapel[] = [
                'mapel' => $ra->nama_mapel,
                'guru' => $ra->guru ?? '-',
                'kkm' => 75,
                'pengetahuan' => round($ra->nilai_akhir),
                'keterampilan' => round($ra->nilai_akhir),
                'deskripsi_tercapai' => $ra->deskripsi_tertinggi ?? '-',
                'deskripsi_peningkatan' => $ra->deskripsi_terendah ?? '-',
                'formatif_tp' => $f_mapel,
                'sumatif' => [
                    'sas' => $sas,
                    'sts' => $sts
                ]
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'semester' => $semester_nama,
                'rata_rata' => round($rata_rata, 1),
                'kenaikan' => 0,
                'kehadiran' => $persen_hadir,
                'peringkat' => $peringkat,
                'total_siswa' => $total_siswa,
                'nilai_mapel' => $nilai_mapel
            ]
        ], 200);
    }
}
