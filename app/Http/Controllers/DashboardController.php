<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->session()->get('role_active', 'admin'); // Default to admin for now if not set

        if ($role === 'admin' || $role === 'kepsek') {
            return $this->adminDashboard();
        } elseif ($role === 'guru') {
            return $this->guruDashboard();
        } elseif ($role === 'siswa') {
            return $this->siswaDashboard();
        }

        return Inertia::render('Dashboard'); // Fallback
    }

    private function adminDashboard()
    {
        // Akademik Dasar
        $totalGuru = Guru::count();
        $totalSiswa = Siswa::whereIn('status_siswa', ['Aktif', 'AKTIF'])->count();
        $totalUser = User::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();
        $totalWaliKelas = Kelas::whereNotNull('guru_id')->count();

        // Keuangan
        $totalBayar = DB::table('tbl_transaksi')->whereIn('status_transaksi', ['SUCCESS', 'PAID', 'LUNAS'])->sum('jumlah_bayar') ?? 0;
        $totalTagihan = DB::table('tbl_tagihan')->sum('nominal_tagihan') ?? 0;
        $totalTerbayar = DB::table('tbl_tagihan')->sum('nominal_terbayar') ?? 0;
        $totalTunggak = $totalTagihan - $totalTerbayar;

        // E-Learning
        $totalUjianAktif = DB::table('tbl_jadwal_ujian')->where('status_ujian', 'AKTIF')->count();
        $totalBankSoal = DB::table('tbl_bank_soal')->count();

        // Kesiswaan & PPDB
        $totalPendaftar = DB::table('tbl_pendaftar')->count();
        $totalPelanggaran = DB::table('tbl_siswa_pelanggaran')->count();

        // Perpustakaan & Surat
        $totalBuku = DB::table('tbl_buku')->count();
        $totalSuratMasuk = DB::table('tbl_surat_masuk')->count();
        $totalSuratKeluar = DB::table('tbl_surat_keluar')->count();
        $totalSurat = $totalSuratMasuk + $totalSuratKeluar;

        // Ekskul & PKL
        $totalEkskul = DB::table('tbl_ekskul')->count();
        $totalPkl = DB::table('tbl_pkl')->count();

        // Persentase KBM
        $hariIni = \Carbon\Carbon::now();
        // Array hari versi bahasa indonesia
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $days[$hariIni->dayOfWeek];
        
        $jadwalHariIni = DB::table('tbl_jadwal')->where('hari', $dayName)->count();
        $jurnalHariIni = DB::table('tbl_jurnal')->whereDate('tanggal', $hariIni->toDateString())->count();
        
        $persentaseKbm = 0;
        if ($jadwalHariIni > 0) {
            $persentaseKbm = round(($jurnalHariIni / $jadwalHariIni) * 100);
            if ($persentaseKbm > 100) $persentaseKbm = 100;
        }

        // Real Data untuk Chart Presensi Mingguan Siswa
        $labels = [];
        $presensi = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); 
            
            $totalKehadiran = DB::table('tbl_presensi')
                ->where('role', 'siswa')
                ->whereDate('tanggal', $date)
                ->count();
            
            $hadir = DB::table('tbl_presensi')
                ->where('role', 'siswa')
                ->whereDate('tanggal', $date)
                ->whereIn('status_kehadiran', ['Tepat Waktu', 'Terlambat'])
                ->count();

            if ($totalKehadiran > 0) {
                $presensi[] = round(($hadir / $totalKehadiran) * 100);
            } else {
                $presensi[] = 0;
            }
        }

        // Pengguna Aktif (Login Terakhir)
        $activeUsers = User::orderBy('updated_at', 'desc')->take(5)->get()->map(function($user) {
            $nama = $user->username;
            if ($user->role === 'guru') {
                $guru = Guru::where('user_id', $user->id)->first();
                if ($guru) $nama = $guru->nama_lengkap;
            } elseif ($user->role === 'siswa') {
                $siswa = Siswa::where('user_id', $user->id)->first();
                if ($siswa) $nama = $siswa->nama_lengkap;
            } elseif ($user->role === 'admin') {
                $nama = 'Administrator Sistem';
            }
            return [
                'name' => $nama,
                'role' => ucfirst($user->role),
                'avatar_name' => str_replace(' ', '+', $nama),
                'last_seen' => $user->updated_at ? $user->updated_at->diffForHumans() : 'Baru saja'
            ];
        });

        // Pemberitahuan Sistem (Recent Activities)
        $recentActivities = [];
        
        $recentPayments = DB::table('tbl_transaksi')
            ->join('tbl_siswa', 'tbl_transaksi.id_siswa', '=', 'tbl_siswa.id')
            ->select('tbl_transaksi.*', 'tbl_siswa.nama_lengkap')
            ->orderBy('tbl_transaksi.created_at', 'desc')
            ->take(2)->get();
            
        foreach ($recentPayments as $payment) {
            $recentActivities[] = [
                'icon' => 'fas fa-wallet',
                'color' => 'text-green-600',
                'bg' => 'bg-green-100',
                'title' => 'Pembayaran Rp ' . number_format($payment->jumlah_bayar, 0, ',', '.'),
                'desc' => $payment->nama_lengkap . ' (' . \Carbon\Carbon::parse($payment->created_at)->diffForHumans() . ')',
            ];
        }

        $recentPpdb = DB::table('tbl_pendaftar')
            ->orderBy('tgl_daftar', 'desc')
            ->take(1)->get();
            
        foreach ($recentPpdb as $p) {
            $recentActivities[] = [
                'icon' => 'fas fa-user-plus',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-100',
                'title' => 'Pendaftar PPDB Baru',
                'desc' => $p->nama_lengkap . ' mendaftar ' . \Carbon\Carbon::parse($p->tgl_daftar)->diffForHumans(),
            ];
        }

        $recentPelanggaran = DB::table('tbl_siswa_pelanggaran')
            ->join('tbl_siswa', 'tbl_siswa_pelanggaran.siswa_id', '=', 'tbl_siswa.id')
            ->select('tbl_siswa_pelanggaran.*', 'tbl_siswa.nama_lengkap')
            ->orderBy('tbl_siswa_pelanggaran.tanggal', 'desc')
            ->take(1)->get();
            
        foreach ($recentPelanggaran as $p) {
            $recentActivities[] = [
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'text-red-600',
                'bg' => 'bg-red-100',
                'title' => 'Pelanggaran Tercatat',
                'desc' => $p->nama_lengkap . ' (' . \Carbon\Carbon::parse($p->tanggal)->diffForHumans() . ')',
            ];
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'guru' => $totalGuru,
                'siswa' => $totalSiswa,
                'user' => $totalUser,
                'kelas' => $totalKelas,
                'mapel' => $totalMapel,
                'wali_kelas' => $totalWaliKelas,
            ],
            'finance' => [
                'total_bayar' => (int) $totalBayar,
                'total_tunggakan' => (int) $totalTunggak,
            ],
            'features' => [
                'ujian_aktif' => $totalUjianAktif,
                'bank_soal' => $totalBankSoal,
                'pendaftar_ppdb' => $totalPendaftar,
                'pelanggaran' => $totalPelanggaran,
                'buku' => $totalBuku,
                'surat' => $totalSurat,
                'ekskul' => $totalEkskul,
                'pkl' => $totalPkl,
            ],
            'kbm' => [
                'jadwal_hari_ini' => $jadwalHariIni,
                'jurnal_terisi' => $jurnalHariIni,
                'persentase' => $persentaseKbm
            ],
            'chart' => [
                'labels' => $labels,
                'presensi' => $presensi,
            ],
            'users' => $activeUsers,
            'activities' => $recentActivities
        ]);
    }

    private function guruDashboard()
    {
        $guruId = auth()->user()->guru->id ?? null;
        $userId = auth()->id();
        
        // 1. CBT
        $totalBankSoal = \App\Models\BankSoal::where('user_id', $userId)->count();
        $totalDraftUjian = \App\Models\DraftUjian::whereHas('bankSoal', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();
        $totalJadwalUjian = \App\Models\JadwalUjian::whereHas('draftUjian.bankSoal', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();

        // 2. KBM & Akademik
        $hariIni = \Carbon\Carbon::now();
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $days[$hariIni->dayOfWeek];
        
        $jadwalHariIni = DB::table('tbl_jadwal')->where('id_guru', $guruId)->where('hari', $dayName)->count();
        $jurnalHariIni = DB::table('tbl_jurnal')->where('id_guru', $guruId)->whereDate('tanggal', $hariIni->toDateString())->count();
        $persentaseKbm = $jadwalHariIni > 0 ? round(($jurnalHariIni / $jadwalHariIni) * 100) : 0;
        if ($persentaseKbm > 100) $persentaseKbm = 100;

        // 3. E-Learning
        $totalTugas = DB::table('tbl_tugas')->where('guru_id', $guruId)->count();
        $totalMateri = DB::table('tbl_materi')->where('guru_id', $guruId)->count();
        $kelasVirtual = DB::table('tbl_kelas_virtual')
            ->where('guru_id', $guruId)
            ->whereDate('tgl_pertemuan', '>=', $hariIni->toDateString())
            ->count();

        // 4. E-Rapor
        $totalTP = DB::table('tbl_tujuan_pembelajaran')->where('guru_id', $guruId)->count();
        $totalFormatif = DB::table('tbl_nilai_formatif')
            ->join('tbl_tujuan_pembelajaran', 'tbl_nilai_formatif.tp_id', '=', 'tbl_tujuan_pembelajaran.id')
            ->where('tbl_tujuan_pembelajaran.guru_id', $guruId)
            ->count();
        $totalSumatif = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('tbl_nilai_sumatif')) {
            $mapelIds = DB::table('tbl_jadwal')->where('id_guru', $guruId)->pluck('id_mapel')->unique();
            $kolomMapel = \Illuminate\Support\Facades\Schema::hasColumn('tbl_nilai_sumatif', 'id_mapel') ? 'id_mapel' : 'mapel_id';
            $totalSumatif = DB::table('tbl_nilai_sumatif')->whereIn($kolomMapel, $mapelIds)->count();
        }

        // 5. Tugas Tambahan
        $disposisiUnread = DB::table('tbl_disposisi')->where('id_penerima', $guruId)->where('is_read', 0)->count();
        $ekskulDibina = DB::table('tbl_ekskul_pembina')->where('guru_id', $guruId)->count();
        $isPiket = DB::table('tbl_jadwal_piket')->where('guru_id', $guruId)->where('hari', $dayName)->exists();

        // Real Data untuk Chart Presensi Kelas yang Diajar (Siswa)
        $labels = [];
        $presensi = [];
        $kelasIds = DB::table('tbl_jadwal')->where('id_guru', $guruId)->pluck('id_kelas')->unique();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); 
            
            $totalKehadiran = DB::table('tbl_presensi')
                ->where('role', 'siswa')
                ->whereIn('user_id', function($q) use ($kelasIds) {
                    $q->select('user_id')->from('tbl_siswa')->whereIn('kelas_id', $kelasIds);
                })
                ->whereDate('tanggal', $date)
                ->count();
            
            $hadir = DB::table('tbl_presensi')
                ->where('role', 'siswa')
                ->whereIn('user_id', function($q) use ($kelasIds) {
                    $q->select('user_id')->from('tbl_siswa')->whereIn('kelas_id', $kelasIds);
                })
                ->whereDate('tanggal', $date)
                ->whereIn('status_kehadiran', ['Tepat Waktu', 'Terlambat'])
                ->count();

            $presensi[] = $totalKehadiran > 0 ? round(($hadir / $totalKehadiran) * 100) : 0;
        }

        return Inertia::render('Guru/Dashboard', [
            'stats' => [
                'cbt' => [
                    'bank_soal' => $totalBankSoal,
                    'draft_ujian' => $totalDraftUjian,
                    'jadwal_ujian' => $totalJadwalUjian,
                ],
                'kbm' => [
                    'jadwal_hari_ini' => $jadwalHariIni,
                    'jurnal_terisi' => $jurnalHariIni,
                    'persentase' => $persentaseKbm
                ],
                'elearning' => [
                    'tugas' => $totalTugas,
                    'materi' => $totalMateri,
                    'kelas_virtual' => $kelasVirtual
                ],
                'erapor' => [
                    'tp' => $totalTP,
                    'formatif' => $totalFormatif,
                    'sumatif' => $totalSumatif
                ],
                'tambahan' => [
                    'disposisi' => $disposisiUnread,
                    'ekskul' => $ekskulDibina,
                    'piket' => $isPiket
                ]
            ],
            'chart' => [
                'labels' => $labels,
                'presensi' => $presensi,
            ]
        ]);
    }

    private function siswaDashboard()
    {
        $siswa = \App\Models\Siswa::with(['kelas', 'jurusan'])->where('user_id', auth()->id())->first();
        $siswaId = $siswa->id ?? null;
        $kelasId = $siswa->kelas_id ?? null;
        
        // 1. E-Learning (Ujian & Tugas)
        $totalUjianDiikuti = \App\Models\UjianSiswa::where('siswa_id', $siswaId)->count();
        $ujianAktif = \App\Models\UjianSiswa::where('siswa_id', $siswaId)
            ->whereHas('jadwal', function($q) {
                $q->where('status_ujian', 'AKTIF');
            })->count();
            
        $tugasBelumSelesai = \App\Models\Tugas::where('kelas_id', $kelasId)
            ->where('status', 1)
            ->whereDoesntHave('kumpul', function($query) use ($siswaId) {
                $query->where('siswa_id', $siswaId);
            })->count();

        // 2. Keuangan (Tabungan & Tagihan)
        $saldoTabungan = \App\Models\Rekening::where('siswa_id', $siswaId)->value('saldo') ?? 0;
        
        $totalTagihanAktif = \App\Models\Tagihan::where('id_siswa', $siswaId)
            ->where('status_bayar', '!=', 'LUNAS')
            ->get()
            ->sum(function($q) {
                return $q->nominal_tagihan - $q->nominal_terbayar;
            });

        // 3. Kedisiplinan (Sisa Poin)
        $poinPelanggaran = \App\Models\SiswaPelanggaran::with('pelanggaran')
            ->where('siswa_id', $siswaId)
            ->get()
            ->sum(function($p) {
                return $p->pelanggaran->poin ?? 0;
            });
            
        $sisaPoin = 100 - $poinPelanggaran;
        
        $setSp = \App\Models\SetSp::first();
        $batasSp1 = $setSp ? $setSp->sp_1 : 50;
        $batasSp2 = $setSp ? $setSp->sp_2 : 30;
        $batasSp3 = $setSp ? $setSp->sp_3 : 0;
        
        $statusSp = 'Aman';
        if ($sisaPoin <= $batasSp3) {
            $statusSp = 'SP 3 (Dikeluarkan)';
        } elseif ($sisaPoin <= $batasSp2) {
            $statusSp = 'SP 2';
        } elseif ($sisaPoin <= $batasSp1) {
            $statusSp = 'SP 1';
        }

        // 4. Kehadiran (Presensi Bulan Ini)
        $bulanIni = date('Y-m');
        $presensiBulanIni = \App\Models\Presensi::where('user_id', $siswaId)
            ->where('role', 'siswa')
            ->where('tanggal', 'like', "$bulanIni%")
            ->get();
            
        $totalHadir = $presensiBulanIni->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Dinas Luar'])->count();
        $totalAlpha = $presensiBulanIni->where('status_kehadiran', 'Alpha')->count();

        // Data Grafik 7 Hari Terakhir
        $labels = [];
        $kehadiran = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); 
            
            // Cek presensi di tanggal ini
            $absenHariIni = \App\Models\Presensi::where('user_id', $siswaId)
                ->where('role', 'siswa')
                ->where('tanggal', $date)
                ->first();
                
            if ($absenHariIni && in_array($absenHariIni->status_kehadiran, ['Hadir', 'Terlambat', 'Dinas Luar'])) {
                $kehadiran[] = 100;
            } else if ($absenHariIni && in_array($absenHariIni->status_kehadiran, ['Sakit', 'Izin'])) {
                $kehadiran[] = 50; // Setengah/Izin
            } else {
                // Jika libur (hari minggu)
                $kodeHari = date('N', strtotime($date));
                if ($kodeHari == 6 || $kodeHari == 7) {
                    $kehadiran[] = 0;
                } else {
                    $kehadiran[] = 0; // Alpha atau belum diabsen
                }
            }
        }

        return Inertia::render('Siswa/Dashboard', [
            'siswa' => $siswa,
            'stats' => [
                'ujian_diikuti' => $totalUjianDiikuti,
                'ujian_aktif' => $ujianAktif,
                'tugas_belum' => $tugasBelumSelesai,
            ],
            'keuangan' => [
                'saldo_tabungan' => $saldoTabungan,
                'tagihan_aktif' => $totalTagihanAktif,
            ],
            'kedisiplinan' => [
                'sisa_poin' => $sisaPoin,
                'status_sp' => $statusSp,
            ],
            'kehadiran' => [
                'hadir' => $totalHadir,
                'alpha' => $totalAlpha,
            ],
            'chart' => [
                'labels' => $labels,
                'kehadiran' => $kehadiran,
            ]
        ]);
    }
}
