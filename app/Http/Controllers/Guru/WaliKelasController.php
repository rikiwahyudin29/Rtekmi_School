<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\RaporKehadiran;
use App\Models\RaporCatatanWali;
use App\Models\RaporPkl;
use App\Models\Dudi;
use App\Models\PklK13;
use App\Models\DeskripsiP3K13;
use App\Models\DeskripsiDplK13;
use App\Models\KenaikanKelas;
use Illuminate\Support\Facades\Auth;

class WaliKelasController extends Controller
{
    /**
     * Mendapatkan kelas di mana guru ini adalah wali kelas.
     * Untuk simulasi, kita kembalikan semua kelas atau kelas ID 1.
     */
    private function getKelasWali()
    {
        $guru_id = Auth::user()->guru->id ?? 1;
        $kelas = Kelas::where('guru_id', $guru_id)->first();
        return $kelas;
    }

    public function index()
    {
        $kelas = $this->getKelasWali();
        
        $status_penilaian = [];
        if ($kelas) {
            $siswa_count = Siswa::where('kelas_id', $kelas->id)->count();
            $jadwal = \App\Models\JadwalPelajaran::with(['mapel', 'guru'])
                ->where('id_kelas', $kelas->id)
                ->get()
                ->unique('id_mapel');

            foreach ($jadwal as $j) {
                if ($j->mapel && $j->guru) {
                    $siswa_ids = Siswa::where('kelas_id', $kelas->id)->pluck('id');
                    $nilai_count = \App\Models\RaporAkhir::where('mapel_id', $j->id_mapel)
                        ->whereIn('siswa_id', $siswa_ids)
                        ->count();

                    $status = 'Belum Tuntas';
                    if ($siswa_count == 0) {
                        $status = 'Belum Ada Siswa';
                    } elseif ($nilai_count >= $siswa_count) {
                        $status = 'Tuntas';
                    } elseif ($nilai_count > 0) {
                        $status = "Proses ($nilai_count/$siswa_count)";
                    }

                    $status_penilaian[] = [
                        'mapel' => $j->mapel->nama_mapel,
                        'guru' => $j->guru->nama_lengkap,
                        'status' => $status,
                        'tuntas' => $status === 'Tuntas'
                    ];
                }
            }
        }
        
        return Inertia::render('Guru/WaliKelas/Index', [
            'kelas' => $kelas,
            'status_penilaian' => $status_penilaian
        ]);
    }

    /**
     * Input Kehadiran Siswa
     */
    public function kehadiran()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        
        $kehadiran = RaporKehadiran::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Kehadiran', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'kehadiran' => $kehadiran
        ]);
    }

    public function storeKehadiran(Request $request)
    {
        $request->validate([
            'data' => 'required|array'
        ]);

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        foreach ($request->data as $siswa_id => $absen) {
            RaporKehadiran::updateOrCreate(
                [
                    'siswa_id' => $siswa_id,
                    'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                    'semester' => 1
                ],
                [
                    'sakit' => $absen['sakit'] ?? 0,
                    'izin' => $absen['izin'] ?? 0,
                    'tanpa_keterangan' => $absen['tanpa_keterangan'] ?? 0,
                ]
            );
        }

        return redirect()->back()->with('success', 'Data kehadiran berhasil disimpan.');
    }

    /**
     * Input Catatan Wali Kelas
     */
    public function catatan()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        
        $catatan = RaporCatatanWali::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Catatan', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'catatan' => $catatan
        ]);
    }

    public function storeCatatan(Request $request)
    {
        $request->validate([
            'data' => 'required|array'
        ]);

        $guru_id = Auth::user()->guru->id ?? 1;
        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        foreach ($request->data as $siswa_id => $catatan_text) {
            if (!empty($catatan_text)) {
                RaporCatatanWali::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'guru_id' => $guru_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => 1
                    ],
                    [
                        'catatan' => $catatan_text
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Catatan Wali Kelas berhasil disimpan.');
    }

    /**
     * Input Data Praktik Kerja Lapangan (PKL)
     */
    public function pkl()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        $dudi_list = Dudi::all();
        
        $pkl = RaporPkl::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Pkl', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'pkl' => $pkl,
            'dudi_list' => $dudi_list
        ]);
    }

    public function storePkl(Request $request)
    {
        $request->validate([
            'data' => 'required|array'
        ]);

        $tahun_ajaran_aktif = TahunAjaran::where('status', 'Aktif')->first();

        foreach ($request->data as $siswa_id => $p) {
            if (!empty($p['dudi_id']) && !empty($p['lokasi']) && !empty($p['lama_bulan'])) {
                RaporPkl::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tahun_ajaran_id' => $tahun_ajaran_aktif->id ?? 1,
                        'semester' => 1
                    ],
                    [
                        'dudi_id' => $p['dudi_id'],
                        'lokasi' => $p['lokasi'],
                        'lama_bulan' => $p['lama_bulan'],
                        'keterangan' => $p['keterangan'] ?? null,
                        'nilai' => $p['nilai'] ?? null,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Data PKL siswa berhasil disimpan.');
    }

    /**
     * Status Kenaikan / Kelulusan Kelas
     */
    public function kenaikan()
    {
        $tahun_ajaran = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
        if ($tahun_ajaran && $tahun_ajaran->semester === 'Ganjil') {
            return redirect()->back()->with('error', 'Status Kenaikan Kelas hanya dapat dikelola pada Semester Genap.');
        }

        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        $kelas_all = Kelas::all();
        
        $kenaikan = KenaikanKelas::whereIn('siswa_id', $siswa->pluck('id'))
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/Kenaikan', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'kenaikan' => $kenaikan,
            'kelas_all' => $kelas_all
        ]);
    }

    public function storeKenaikan(Request $request)
    {
        $tahun_ajaran = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
        if ($tahun_ajaran && $tahun_ajaran->semester === 'Ganjil') {
            return redirect()->back()->with('error', 'Status Kenaikan Kelas hanya dapat dikelola pada Semester Genap.');
        }

        $request->validate([
            'data' => 'required|array'
        ]);

        foreach ($request->data as $siswa_id => $k) {
            if (!empty($k['status'])) {
                KenaikanKelas::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id
                    ],
                    [
                        'status' => $k['status'],
                        'kelas_tujuan_id' => $k['kelas_tujuan_id'] ?? null
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Status Kenaikan Kelas berhasil disimpan.');
    }

    public function cetakRapor()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();

        return Inertia::render('Guru/WaliKelas/Cetak', [
            'kelas' => $kelas,
            'siswa' => $siswa
        ]);
    }

    /**
     * K13: PKL K13
     */
    public function pklK13()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        $pkl_k13 = PklK13::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/PklK13', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'pkl_k13' => $pkl_k13
        ]);
    }

    public function storePklK13(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $p) {
            if (!empty($p['mitra_du_di'])) {
                PklK13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'semester' => 1],
                    [
                        'mitra_du_di' => $p['mitra_du_di'],
                        'lokasi' => $p['lokasi'] ?? '-',
                        'lama_bulan' => $p['lama_bulan'] ?? 0,
                        'keterangan' => $p['keterangan'] ?? null,
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Data PKL K13 berhasil disimpan.');
    }

    /**
     * K13: Deskripsi P3 K13
     */
    public function deskripsiP3()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        $deskripsi_p3 = DeskripsiP3K13::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/DeskripsiP3', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'deskripsi_p3' => $deskripsi_p3
        ]);
    }

    public function storeDeskripsiP3(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $d) {
            if (!empty($d['deskripsi'])) {
                DeskripsiP3K13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'semester' => 1],
                    ['deskripsi' => $d['deskripsi']]
                );
            }
        }
        return redirect()->back()->with('success', 'Deskripsi Profil Pelajar Pancasila (K13) berhasil disimpan.');
    }

    /**
     * K13: Deskripsi DPL K13
     */
    public function deskripsiDpl()
    {
        $kelas = $this->getKelasWali();
        $siswa = Siswa::where('kelas_id', $kelas->id ?? 0)->get();
        $deskripsi_dpl = DeskripsiDplK13::whereIn('siswa_id', $siswa->pluck('id'))
                        ->where('semester', 1)
                        ->get()->keyBy('siswa_id');

        return Inertia::render('Guru/WaliKelas/DeskripsiDpl', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'deskripsi_dpl' => $deskripsi_dpl
        ]);
    }

    public function storeDeskripsiDpl(Request $request)
    {
        $request->validate(['data' => 'required|array']);

        foreach ($request->data as $siswa_id => $d) {
            if (!empty($d['deskripsi'])) {
                DeskripsiDplK13::updateOrCreate(
                    ['siswa_id' => $siswa_id, 'semester' => 1],
                    ['deskripsi' => $d['deskripsi']]
                );
            }
        }
        return redirect()->back()->with('success', 'Deskripsi Perkembangan Lulusan (K13) berhasil disimpan.');
    }
}
