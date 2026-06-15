<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PklKelompok;
use App\Models\PklTp;
use Illuminate\Support\Facades\Auth;

class PklController extends Controller
{
    // ==========================================
    // DASHBOARD PERFORMA & KPI (GURU)
    // ==========================================
    public function index()
    {
        $guru_id = Auth::user()->guru->id ?? 1;

        $siswa_binaan = DB::table('tbl_pkl')
            ->select('tbl_pkl.*', 'tbl_siswa.nama_lengkap as nama_siswa', 'tbl_siswa.nis', 'tbl_dudi.nama_dudi', 'tbl_dudi.id as dudi_id')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->where('tbl_pkl.guru_id', $guru_id)
            ->where('tbl_pkl.status_pkl', 'Aktif')
            ->get();

        $data_siswa = [];
        $dudi_list = [];
        $total_siswa_lulus = 0;

        foreach ($siswa_binaan as $s) {
            $start = strtotime($s->tgl_mulai);
            $end = strtotime($s->tgl_selesai);
            $target_hari = 0;
            for ($i = $start; $i <= $end; $i += 86400) {
                if (date('w', $i) != 0 && date('w', $i) != 6) $target_hari++;
            }
            if($target_hari == 0) $target_hari = 1;

            $hadir = DB::table('tbl_pkl_absen')->where('pkl_id', $s->id)->where('status', 'Hadir')->count();
            $p_hadir = min(($hadir / $target_hari) * 100, 100);

            $jurnal_acc = DB::table('tbl_pkl_jurnal')->where('pkl_id', $s->id)->where('status_jurnal', 'Disetujui')->count();
            $p_jurnal = min(($jurnal_acc / $target_hari) * 100, 100);

            $laporan = DB::table('tbl_pkl_laporan')->where('pkl_id', $s->id)->first();
            $p_laporan = 0; $status_lap = 'Belum Ada'; $badge_lap = 'bg-gray-100 text-gray-500';
            if ($laporan) {
                if ($laporan->status_laporan == 'Disetujui') { $p_laporan = 100; $status_lap = 'ACC'; $badge_lap = 'bg-emerald-100 text-emerald-700'; }
                elseif ($laporan->status_laporan == 'Revisi') { $p_laporan = 50; $status_lap = 'Revisi'; $badge_lap = 'bg-rose-100 text-rose-700'; }
                else { $p_laporan = 25; $status_lap = 'Review'; $badge_lap = 'bg-blue-100 text-blue-700'; }
            }

            $nilai_sistem = ($p_hadir * 0.4) + ($p_jurnal * 0.4) + ($p_laporan * 0.2);
            if($nilai_sistem >= 90) $grade = 'A';
            elseif($nilai_sistem >= 80) $grade = 'B';
            elseif($nilai_sistem >= 70) $grade = 'C';
            elseif($nilai_sistem >= 60) $grade = 'D';
            else $grade = 'E';

            $nilai_resmi = DB::table('tbl_pkl_nilai')->where('pkl_id', $s->id)->first();
            if ($nilai_resmi) $total_siswa_lulus++;

            $data_siswa[] = [
                'id_pkl' => $s->id,
                'nama_siswa' => $s->nama_siswa,
                'nis' => $s->nis,
                'nama_dudi' => $s->nama_dudi,
                'p_hadir' => round($p_hadir, 1),
                'p_jurnal' => round($p_jurnal, 1),
                'p_laporan' => $p_laporan,
                'status_lap' => $status_lap,
                'badge_lap' => $badge_lap,
                'nilai_sistem' => round($nilai_sistem, 1),
                'grade' => $grade,
                'is_lulus' => $nilai_resmi ? true : false,
                'nilai_akhir' => $nilai_resmi->nilai_akhir ?? 0
            ];

            $dudi_list[$s->dudi_id] = $s->nama_dudi;
        }

        $data_kunjungan = [];
        $total_target_kunjungan = count($dudi_list) * 3;
        $total_realisasi_kunjungan = 0;

        foreach ($dudi_list as $id_dudi => $nama_dudi) {
            $jml_kunjungan = DB::table('tbl_pkl_kunjungan')
                ->where('guru_id', $guru_id)
                ->where('dudi_id', $id_dudi)
                ->count();
            
            $tercapai = min($jml_kunjungan, 3);
            $total_realisasi_kunjungan += $tercapai;

            $data_kunjungan[] = [
                'nama_dudi' => $nama_dudi,
                'jml_kunjungan' => $jml_kunjungan,
                'persen' => ($tercapai / 3) * 100
            ];
        }

        $persen_kunjungan = $total_target_kunjungan > 0 ? ($total_realisasi_kunjungan / $total_target_kunjungan) * 100 : 0;

        return Inertia::render('Guru/Pkl/Index', [
            'data_siswa' => $data_siswa,
            'data_kunjungan' => $data_kunjungan,
            'total_siswa' => count($siswa_binaan),
            'total_lulus' => $total_siswa_lulus,
            'total_dudi' => count($dudi_list),
            'persen_kunjungan' => round($persen_kunjungan, 1)
        ]);
    }

    public function monitoring()
    {
        $guru_id = Auth::user()->guru->id ?? 1;

        $siswa_binaan = DB::table('tbl_pkl')
            ->select('tbl_pkl.*', 'tbl_siswa.nama_lengkap as nama_siswa', 'tbl_siswa.nis', 'tbl_dudi.nama_dudi', 'tbl_dudi.latitude as dudi_lat', 'tbl_dudi.longitude as dudi_long', 'tbl_dudi.radius_absen')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->where('tbl_pkl.guru_id', $guru_id)
            ->where('tbl_pkl.status_pkl', 'Aktif')
            ->get();

        $tanggal_hari_ini = date('Y-m-d');
        $absen_hari_ini = [];
        
        if ($siswa_binaan->count() > 0) {
            $pkl_ids = $siswa_binaan->pluck('id')->toArray();
            $absen = DB::table('tbl_pkl_absen')
                ->whereIn('pkl_id', $pkl_ids)
                ->where('tanggal', $tanggal_hari_ini)
                ->get();
                              
            foreach ($absen as $a) {
                $absen_hari_ini[$a->pkl_id] = $a;
            }
        }

        return Inertia::render('Guru/Pkl/Monitoring', [
            'siswa_binaan' => $siswa_binaan,
            'absen_hari_ini' => $absen_hari_ini,
            'tanggal' => $tanggal_hari_ini
        ]);
    }

    public function jurnal()
    {
        $guru_id = Auth::user()->guru->id ?? 1;

        $jurnal = DB::table('tbl_pkl_jurnal')
            ->select('tbl_pkl_jurnal.*', 'tbl_siswa.nama_lengkap as nama_siswa', 'tbl_siswa.nis', 'tbl_dudi.nama_dudi')
            ->join('tbl_pkl', 'tbl_pkl.id', '=', 'tbl_pkl_jurnal.pkl_id')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->where('tbl_pkl.guru_id', $guru_id)
            ->orderBy('tbl_pkl_jurnal.status_jurnal', 'asc')
            ->orderBy('tbl_pkl_jurnal.tanggal', 'desc')
            ->get();

        $laporan_akhir = DB::table('tbl_pkl_laporan')
            ->select('tbl_pkl_laporan.*', 'tbl_siswa.nama_lengkap as nama_siswa', 'tbl_dudi.nama_dudi')
            ->join('tbl_pkl', 'tbl_pkl.id', '=', 'tbl_pkl_laporan.pkl_id')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->where('tbl_pkl.guru_id', $guru_id)
            ->orderBy('tbl_pkl_laporan.status_laporan', 'asc')
            ->orderBy('tbl_pkl_laporan.tgl_upload', 'desc')
            ->get();

        return Inertia::render('Guru/Pkl/Jurnal', [
            'jurnal' => $jurnal,
            'laporan_akhir' => $laporan_akhir
        ]);
    }

    public function jurnalValidasi(Request $request)
    {
        $id = $request->input('id');
        DB::table('tbl_pkl_jurnal')->where('id', $id)->update([
            'status_jurnal' => $request->input('status_jurnal'),
            'komentar_guru' => $request->input('komentar_guru')
        ]);
        return redirect()->back()->with('message', 'Jurnal berhasil divalidasi!');
    }

    public function laporanValidasi(Request $request)
    {
        $id = $request->input('id');
        DB::table('tbl_pkl_laporan')->where('id', $id)->update([
            'status_laporan' => $request->input('status_laporan'),
            'catatan_revisi' => $request->input('catatan_revisi')
        ]);
        return redirect()->back()->with('message', 'Status laporan berhasil diperbarui!');
    }

    public function kunjungan()
    {
        $guru_id = Auth::user()->guru->id ?? 1;

        $riwayat = DB::table('tbl_pkl_kunjungan')
            ->select('tbl_pkl_kunjungan.*', 'tbl_dudi.nama_dudi')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl_kunjungan.dudi_id')
            ->where('tbl_pkl_kunjungan.guru_id', $guru_id)
            ->orderBy('tbl_pkl_kunjungan.tanggal', 'desc')
            ->get();

        $list_dudi = DB::table('tbl_pkl')
            ->select('tbl_dudi.id', 'tbl_dudi.nama_dudi')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->where('tbl_pkl.guru_id', $guru_id)
            ->groupBy('tbl_pkl.dudi_id', 'tbl_dudi.id', 'tbl_dudi.nama_dudi')
            ->get();

        return Inertia::render('Guru/Pkl/Kunjungan', [
            'riwayat' => $riwayat,
            'list_dudi' => $list_dudi
        ]);
    }

    public function kunjunganSimpan(Request $request)
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $foto_base64 = $request->input('foto_kunjungan_base64');
        $namaFoto = null;

        if ($foto_base64) {
            $image_parts = explode(";base64,", $foto_base64);
            if(count($image_parts) > 1) {
                $image_base64 = base64_decode($image_parts[1]);
                $namaFoto = 'VISIT_' . time() . '.png';
                $dir = public_path('uploads/kunjungan_pkl');
                if (!file_exists($dir)) mkdir($dir, 0777, true);
                file_put_contents($dir . '/' . $namaFoto, $image_base64);
            }
        }

        DB::table('tbl_pkl_kunjungan')->insert([
            'guru_id' => $guru_id,
            'dudi_id' => $request->input('dudi_id'),
            'tanggal' => $request->input('tanggal'),
            'catatan' => $request->input('catatan'),
            'latitude' => $request->input('lat'),
            'longitude' => $request->input('long'),
            'foto_kunjungan' => $namaFoto
        ]);

        return redirect()->back()->with('message', 'Jurnal kunjungan monitoring berhasil disimpan!');
    }

    public function nilai()
    {
        $guru_id = Auth::user()->guru->id ?? 1;

        $siswa = DB::table('tbl_pkl')
            ->select('tbl_pkl.*', 'tbl_siswa.nama_lengkap as nama_siswa', 'tbl_siswa.nis', 'tbl_dudi.nama_dudi', 'tbl_pkl_laporan.status_laporan', 'tbl_pkl_nilai.nilai_akhir', 'tbl_pkl_nilai.predikat')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
            ->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id')
            ->leftJoin('tbl_pkl_laporan', 'tbl_pkl_laporan.pkl_id', '=', 'tbl_pkl.id')
            ->leftJoin('tbl_pkl_nilai', 'tbl_pkl_nilai.pkl_id', '=', 'tbl_pkl.id')
            ->where('tbl_pkl.guru_id', $guru_id)
            ->get();

        return Inertia::render('Guru/Pkl/Nilai', [
            'siswa' => $siswa
        ]);
    }

    public function simpanNilai(Request $request)
    {
        $pkl_id = $request->input('pkl_id');
        $nilai_sekolah = $request->input('nilai_sekolah');
        $nilai_dudi = $request->input('nilai_dudi');
        
        // Simpelisasi logic penilaian PKL: Gabungan nilai sekolah (laporan) dan DUDI (sikap/teknis)
        $na = ($nilai_sekolah + $nilai_dudi) / 2;

        if($na >= 90) $pred = 'A (Istimewa)';
        elseif($na >= 80) $pred = 'B (Baik)';
        elseif($na >= 70) $pred = 'C (Cukup)';
        else $pred = 'D (Kurang)';

        $token = md5(uniqid(rand(), true));
        $pkl = DB::table('tbl_pkl')->where('id', $pkl_id)->first();
        
        $no_urut = DB::table('tbl_surat_keluar')->count() + 1;
        $no_sertif = str_pad($no_urut, 3, '0', STR_PAD_LEFT) . "/SRT-PKL/" . date('Y');

        // Input / Update Surat Keluar
        $cek_surat = DB::table('tbl_surat_keluar')->where('no_surat', $no_sertif)->first();
        if(!$cek_surat) {
            DB::table('tbl_surat_keluar')->insert([
                'no_surat' => $no_sertif,
                'siswa_id' => $pkl->siswa_id,
                'perihal'  => 'Sertifikat Praktik Kerja Lapangan',
                'tgl_surat'=> date('Y-m-d'),
                'token_validasi' => $token,
                'status'   => 'Disetujui'
            ]);
        }

        $data_nilai = [
            'pkl_id' => $pkl_id,
            'no_sertifikat' => $no_sertif,
            'nilai_akhir' => round($na, 2),
            'predikat' => $pred,
            'token_sertifikat' => $token
        ];

        $cek = DB::table('tbl_pkl_nilai')->where('pkl_id', $pkl_id)->first();
        if($cek) DB::table('tbl_pkl_nilai')->where('id', $cek->id)->update($data_nilai);
        else DB::table('tbl_pkl_nilai')->insert($data_nilai);

        DB::table('tbl_pkl')->where('id', $pkl_id)->update([
            'nilai_dudi' => $nilai_dudi,
            'nilai_sekolah' => $nilai_sekolah,
            'status_pkl' => 'Selesai'
        ]);

        // Sinkronisasi otomatis ke tabel RaporPkl agar Wali Kelas bisa langsung cetak Rapor
        $dudi = DB::table('tbl_dudi')->where('id', $pkl->dudi_id)->first();
        $tahun_ajaran_aktif = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
        
        \App\Models\RaporPkl::updateOrCreate(
            [
                'siswa_id' => $pkl->siswa_id,
                'semester' => ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1
            ],
            [
                'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                'dudi_id' => $pkl->dudi_id,
                'lokasi' => $dudi->alamat_dudi ?? '-',
                'lama_bulan' => 6, // default
                'keterangan' => 'Telah melaksanakan PKL dengan sangat baik.',
                'nilai' => $na, // Nilai rata-rata dari sistem
            ]
        );

        return redirect()->back()->with('message', 'Penilaian PKL dan E-Sertifikat berhasil diterbitkan!');
    }
}
