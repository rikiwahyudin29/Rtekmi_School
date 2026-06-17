<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\RaporAkhir;
use App\Models\RaporKehadiran;
use App\Models\RaporCatatanWali;
use App\Models\RaporPkl;
use App\Models\P5Nilai;
use App\Models\UkkNilai;
use App\Models\Kelas;
use App\Models\EkskulNilai;
use App\Models\Sekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;

class CetakRaporController extends Controller
{
    /**
     * Cetak Cover Depan Rapor
     */
    public function cetakCover($id)
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($id);
        $sekolah = Sekolah::first();
        
        // Di aplikasi riil, akan menggunakan view HTML khusus cetak yang di-styling mirip eRapor.
        // Bisa menggunakan dompdf atau print window javascript.
        return view('rapor.cetak_cover', compact('siswa', 'sekolah'));
    }

    /**
     * Cetak Halaman Nilai Rapor (Kurikulum Merdeka)
     */
    public function cetakNilai($id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $tanggal_rapor = Carbon::now()->translatedFormat('d F Y');
        $semester_int = ($tahun_ajaran && $tahun_ajaran->semester === 'Genap') ? 2 : 1;

        $siswa = Siswa::with(['kelas.waliKelas', 'jurusan'])->findOrFail($id);
        $rapor_akhir = RaporAkhir::with('mapel')->where('siswa_id', $id)->where('semester', $semester_int)->get();
        $kehadiran = RaporKehadiran::where('siswa_id', $id)->where('semester', $semester_int)->first();
        $catatan = RaporCatatanWali::where('siswa_id', $id)->where('semester', $semester_int)->first();
        $pkl = RaporPkl::with('dudi')->where('siswa_id', $id)->where('semester', $semester_int)->get();
        $ekskul = EkskulNilai::with('ekskul')->where('siswa_id', $id)->where('semester', $semester_int)->get();
        $kenaikan = \App\Models\KenaikanKelas::with('kelasTujuan')->where('siswa_id', $id)->first();

        return view('rapor.cetak_nilai', compact('siswa', 'rapor_akhir', 'kehadiran', 'catatan', 'pkl', 'ekskul', 'sekolah', 'tahun_ajaran', 'tanggal_rapor', 'kenaikan'));
    }

    /**
     * Cetak Rapor Projek Penguatan Profil Pelajar Pancasila (P5)
     */
    public function cetakP5($id)
    {
        $siswa = Siswa::with(['kelas'])->findOrFail($id);
        $nilai_p5 = P5Nilai::with(['subElemen.elemen.dimensi', 'kelompok.projek.tema'])
                        ->where('siswa_id', $id)
                        ->get();
        
        return view('rapor.cetak_p5', compact('siswa', 'nilai_p5'));
    }

    /**
     * Cetak Sertifikat UKK (Skill Passport)
     */
    public function cetakUkk($id)
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($id);
        $ukk = UkkNilai::with(['paket.jurusan', 'asesorInternal', 'asesorEksternal'])
                        ->where('siswa_id', $id)
                        ->first();
                        
        return view('rapor.cetak_ukk', compact('siswa', 'ukk'));
    }

    /**
     * Cetak Leger Kelas
     */
    public function cetakLeger($kelas_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        $siswa = Siswa::where('kelas_id', $kelas_id)->get();
        $rapor_akhir = RaporAkhir::with('mapel')->whereIn('siswa_id', $siswa->pluck('id'))->get();
        $kehadiran = RaporKehadiran::whereIn('siswa_id', $siswa->pluck('id'))->get();
        
        return view('rapor.cetak_leger', compact('kelas', 'siswa', 'rapor_akhir', 'kehadiran'));
    }

    /**
     * Cetak Buku Induk / Transkrip
     */
    public function cetakPelengkap($id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($id);
        
        return view('rapor.cetak_pelengkap', compact('siswa', 'sekolah', 'tahun_ajaran'));
    }

    /**
     * Cetak Cover Depan Rapor Masal
     */
    public function cetakCoverMasal($kelas_id)
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->where('kelas_id', $kelas_id)->get();
        $sekolah = Sekolah::first();
        
        return view('rapor.masal_cover', compact('siswas', 'sekolah'));
    }

    /**
     * Cetak Pelengkap Rapor Masal
     */
    public function cetakPelengkapMasal($kelas_id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $siswas = Siswa::with(['kelas', 'jurusan'])->where('kelas_id', $kelas_id)->get();
        
        return view('rapor.masal_pelengkap', compact('siswas', 'sekolah', 'tahun_ajaran'));
    }

    /**
     * Cetak Nilai Rapor Masal
     */
    public function cetakNilaiMasal($kelas_id)
    {
        $sekolah = Sekolah::first();
        $tahun_ajaran = TahunAjaran::where('status', 'Aktif')->first();
        $tanggal_rapor = Carbon::now()->translatedFormat('d F Y');
        $semester_int = ($tahun_ajaran && $tahun_ajaran->semester === 'Genap') ? 2 : 1;

        $siswas = Siswa::with(['kelas.waliKelas', 'jurusan'])->where('kelas_id', $kelas_id)->get();
        
        // Fetch all necessary relations for these students
        $rapor_akhir_all = collect();
        $kehadiran_all = collect();
        $catatan_all = collect();
        $pkl_all = collect();
        $ekskul_all = collect();
        $kenaikan_all = collect();
        
        foreach($siswas as $siswa) {
            $rapor_akhir_all[$siswa->id] = RaporAkhir::with('mapel')->where('siswa_id', $siswa->id)->where('semester', $semester_int)->get();
            $kehadiran_all[$siswa->id] = RaporKehadiran::where('siswa_id', $siswa->id)->where('semester', $semester_int)->first();
            $catatan_all[$siswa->id] = RaporCatatanWali::where('siswa_id', $siswa->id)->where('semester', $semester_int)->first();
            $pkl_all[$siswa->id] = RaporPkl::with('dudi')->where('siswa_id', $siswa->id)->where('semester', $semester_int)->get();
            $ekskul_all[$siswa->id] = EkskulNilai::with('ekskul')->where('siswa_id', $siswa->id)->where('semester', $semester_int)->get();
            $kenaikan_all[$siswa->id] = \App\Models\KenaikanKelas::with('kelasTujuan')->where('siswa_id', $siswa->id)->first();
        }

        return view('rapor.masal_nilai', compact('siswas', 'rapor_akhir_all', 'kehadiran_all', 'catatan_all', 'pkl_all', 'ekskul_all', 'sekolah', 'tahun_ajaran', 'tanggal_rapor', 'kenaikan_all'));
    }
}
