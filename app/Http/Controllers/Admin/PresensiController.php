<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\JamSekolah;
use App\Models\Sekolah;
use App\Services\WaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    private $wa;

    public function __construct()
    {
        $this->wa = new WaService();
    }

    // 1. TAMPILAN SCANNER (WEB)
    public function scanner()
    {
        return Inertia::render('Admin/Presensi/Scanner');
    }

    // 2. LOGIKA UTAMA SCANNER KIOSK
    public function prosesScan(Request $request)
    {
        $kode = $request->input('kode');
        $tipe_input = $request->input('tipe') ?? 'QR';

        if (empty($kode)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode tidak terbaca!'
            ]);
        }

        $tgl_hari_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        $role = '';
        $nama = '';
        $nomorWA = '';
        $userID = 0;

        // 1. Cek di Tabel SISWA
        $siswa = Siswa::where('qr_code', $kode)
                      ->orWhere('rfid_uid', $kode)
                      ->orWhere('nis', $kode)
                      ->first();

        if ($siswa) {
            $role = 'siswa';
            $userID = $siswa->id;
            $nama = $siswa->nama_lengkap;
            $nomorWA = $siswa->no_hp_siswa ?? '';
        } else {
            // 2. Cek di Tabel GURU
            $guru = Guru::where('qr_code', $kode)
                        ->orWhere('rfid_uid', $kode)
                        ->orWhere('nik', $kode)
                        ->first();

            if ($guru) {
                $role = 'guru';
                $userID = $guru->id;
                $nama = $guru->nama_guru ?? $guru->nama_lengkap ?? $guru->nama ?? 'Guru';
                $nomorWA = $guru->no_hp ?? '';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan!'
                ]);
            }
        }

        // B. AMBIL PENGATURAN JAM
        $jamSetting = JamSekolah::find(1);
        if (!$jamSetting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengaturan jam sekolah belum ada!'
            ]);
        }

        // C. CEK HARI LIBUR & WEEKEND
        $libur = \App\Models\HariLibur::cekIsLibur($tgl_hari_ini);

        if ($libur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hari ini libur (' . $libur . '). Presensi ditutup!'
            ]);
        }

        // D. CEK PRESENSI
        $cekAbsen = Presensi::where('user_id', $userID)
                            ->where('role', $role)
                            ->where('tanggal', $tgl_hari_ini)
                            ->first();

        if (!$cekAbsen) {
            // MASUK
            $batas_scan = $jamSetting->batas_scan_masuk ?? $jamSetting->jam_masuk_akhir;
            $mulai_terlambat = $jamSetting->jam_masuk_mulai_terlambat ?? '07:00:00';

            if ($jam_sekarang > $batas_scan) {
                return response()->json([
                    'status' => 'error', 'message' => 'Batas waktu scan absen masuk telah lewat (' . $batas_scan . ').'
                ]);
            }

            $status = 'Hadir';
            $menit_terlambat = 0;

            if ($jam_sekarang > $mulai_terlambat) {
                $status = 'Terlambat';
                $mulai_time = strtotime($mulai_terlambat);
                $sekarang_time = strtotime($jam_sekarang);
                $menit_terlambat = floor(($sekarang_time - $mulai_time) / 60);
            }

            Presensi::create([
                'user_id' => $userID,
                'role' => $role,
                'tanggal' => $tgl_hari_ini,
                'jam_masuk' => $jam_sekarang,
                'status_kehadiran' => $status,
                'menit_terlambat' => $menit_terlambat,
                'metode' => $tipe_input,
                'status_verifikasi' => 'Disetujui'
            ]);

            // Kirim WA
            if ($role == 'siswa' && !empty($nomorWA)) {
                $this->wa->kirim($nomorWA, "📢 *INFO PRESENSI*\n\nAnak Anda ($nama) telah TIBA di sekolah.\n🕒 $jam_sekarang\nℹ️ Status: *$status*");
            }

            return response()->json([
                'status' => 'success', 'tipe' => 'MASUK', 'nama' => $nama,
                'jam' => $jam_sekarang, 'ket' => $status
            ]);
        } else {
            // PULANG
            if ($cekAbsen->jam_pulang != null) {
                return response()->json([
                    'status' => 'error', 'message' => 'Sudah absen pulang!'
                ]);
            }

            $cekAbsen->update(['jam_pulang' => $jam_sekarang]);

            if ($role == 'siswa' && !empty($nomorWA)) {
                $this->wa->kirim($nomorWA, "📢 *INFO KEPULANGAN*\n\nAnak Anda ($nama) telah PULANG dari sekolah.\n🕒 $jam_sekarang");
            }

            return response()->json([
                'status' => 'success', 'tipe' => 'PULANG', 'nama' => $nama,
                'jam' => $jam_sekarang, 'ket' => 'Hati-hati di jalan'
            ]);
        }
    }

    // 3. IZIN (Verifikasi dan Tambah Manual)
    public function izin(Request $request)
    {
        $tanggal = $request->get('tanggal') ?? date('Y-m-d');

        $izinSiswa = Presensi::with('siswa.kelas')
            ->where('role', 'siswa')
            ->whereIn('status_kehadiran', ['Izin', 'Sakit', 'Alpha', 'Hadir', 'Terlambat'])
            ->orderBy('created_at', 'desc')
            ->get();

        $izinGuru = Presensi::with('guru')
            ->where('role', 'guru')
            ->whereIn('status_kehadiran', ['Izin', 'Sakit', 'Dinas Luar', 'Alpha', 'Hadir', 'Terlambat'])
            ->orderBy('created_at', 'desc')
            ->get();

        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $guruList = Guru::orderBy('nama_lengkap', 'asc')->get(['id', 'nama_lengkap', 'nik']);

        return Inertia::render('Admin/Presensi/Izin', [
            'izinSiswa' => $izinSiswa,
            'izinGuru' => $izinGuru,
            'tanggal' => $tanggal,
            'kelas' => $kelas,
            'guruList' => $guruList
        ]);
    }

    public function simpanIzin(Request $request)
    {
        $role = $request->input('role') ?? 'siswa';
        $user_id = $role == 'guru' ? $request->input('id_guru') : $request->input('id_siswa');
        $status = $request->input('status');
        $keterangan = $request->input('keterangan');
        $tanggal = $request->input('tanggal') ?? date('Y-m-d');

        $namaFile = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/surat_izin'), $namaFile);
        }

        $cek = Presensi::where('user_id', $user_id)
            ->where('role', $role)
            ->where('tanggal', $tanggal)
            ->count();

        if ($cek > 0) {
            return redirect()->back()->with('error', 'Orang ini sudah memiliki presensi pada hari tersebut.');
        }

        $menit_terlambat = 0;
        if ($status == 'Terlambat') {
            // Bisa diisi default atau sesuai setting, sementara diisi 0 karena manual input bisa disesuaikan nanti
            $menit_terlambat = 15; 
        }

        Presensi::create([
            'user_id' => $user_id,
            'role' => $role,
            'tanggal' => $tanggal,
            'jam_masuk' => date('H:i:s'),
            'status_kehadiran' => $status,
            'menit_terlambat' => $menit_terlambat,
            'metode' => 'Manual',
            'status_verifikasi' => 'Disetujui',
            'keterangan' => $keterangan,
            'bukti_izin' => $namaFile
        ]);

        if ($role == 'siswa') {
            $siswa = Siswa::find($user_id);
            if ($siswa && !empty($siswa->no_hp_siswa)) {
                $pesan = "📢 *INFO PRESENSI*\n\nStatus siswa a.n *$siswa->nama_lengkap* tanggal $tanggal tercatat: *$status*.\n📝 Ket: $keterangan\n\n_Terima kasih._";
                $this->wa->kirim($siswa->no_hp_siswa, $pesan);
            }
        }

        return redirect()->back()->with('success', 'Data presensi manual berhasil ditambahkan.');
    }

    public function hapusIzin($id)
    {
        $presensi = Presensi::find($id);
        if ($presensi) {
            if ($presensi->bukti_izin) {
                $path = public_path('uploads/surat_izin/' . $presensi->bukti_izin);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            $presensi->delete();
        }
        return redirect()->back()->with('success', 'Data presensi berhasil dihapus.');
    }

    // 4. PRESENSI MANUAL
    public function manual(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        return Inertia::render('Admin/Presensi/Manual', [
            'kelas' => $kelas
        ]);
    }

    public function getBelumAbsen(Request $request)
    {
        $role = $request->get('role') ?? 'siswa';
        $tanggal = $request->get('tanggal') ?? date('Y-m-d');
        $id_kelas = $request->get('id_kelas');

        // Cek Libur
        $libur = \App\Models\HariLibur::cekIsLibur($tanggal);
        if ($libur) {
            return response()->json([
                'status' => 'libur',
                'message' => 'Tanggal ini adalah hari libur: ' . $libur,
                'data' => []
            ]);
        }

        $sudah_absen = Presensi::where('role', $role)->where('tanggal', $tanggal)->get()->keyBy('user_id');

        if ($role === 'siswa') {
            if (!$id_kelas) {
                return response()->json(['status' => 'success', 'data' => []]);
            }
            $pengguna = Siswa::with('kelas')->where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get();
        } else {
            $pengguna = Guru::orderBy('nama_lengkap', 'asc')->get();
        }

        $result = [];
        foreach ($pengguna as $p) {
            $absen = $sudah_absen->get($p->id);
            $p->status_presensi = $absen ? $absen->status_kehadiran : 'Belum Absen';
            $p->jam_masuk_absen = $absen ? $absen->jam_masuk : null;
            $result[] = $p;
        }

        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function simpanManualAjax(Request $request)
    {
        $role = $request->input('role') ?? 'siswa';
        $user_id = $request->input('user_id');
        $status = $request->input('status_kehadiran');
        $tanggal = $request->input('tanggal') ?? date('Y-m-d');
        $jam_masuk = $request->input('jam_masuk');

        $cek = Presensi::where('user_id', $user_id)
            ->where('role', $role)
            ->where('tanggal', $tanggal)
            ->first();

        $menit_terlambat = 0;
        if ($status == 'Hadir' || $status == 'Terlambat') {
            if (!$jam_masuk) $jam_masuk = date('H:i:s');
            
            // Ambil setting jam
            $jamSetting = JamSekolah::find(1);
            $mulai_terlambat = $jamSetting->jam_masuk_mulai_terlambat ?? '07:00:00';
            
            if ($jam_masuk > $mulai_terlambat) {
                $status = 'Terlambat';
                $mulai_time = strtotime($mulai_terlambat);
                $sekarang_time = strtotime($jam_masuk);
                $menit_terlambat = floor(($sekarang_time - $mulai_time) / 60);
                if ($menit_terlambat < 0) $menit_terlambat = 0;
            } else {
                $status = 'Hadir';
            }
        } else {
            $jam_masuk = null;
        }

        $dataToSave = [
            'user_id' => $user_id,
            'role' => $role,
            'tanggal' => $tanggal,
            'jam_masuk' => $jam_masuk,
            'status_kehadiran' => $status,
            'menit_terlambat' => $menit_terlambat,
            'metode' => 'Manual',
            'status_verifikasi' => 'Disetujui',
        ];

        if ($cek) {
            $cek->update($dataToSave);
        } else {
            Presensi::create($dataToSave);
        }

        return response()->json(['success' => true, 'message' => 'Data tersimpan.']);
    }

    public function hapusManual($id)
    {
        $presensi = Presensi::find($id);
        if ($presensi) {
            $presensi->delete();
        }
        return redirect()->back()->with('success', 'Data presensi manual berhasil dihapus.');
    }

    public function verifikasi($id, $status)
    {
        $presensi = Presensi::find($id);
        if ($presensi) {
            $presensi->update(['status_verifikasi' => $status]);
        }
        return redirect()->back()->with('success', 'Status pengajuan berhasil diubah menjadi ' . $status);
    }

    public function getSiswaByKelas($id_kelas)
    {
        $siswa = Siswa::where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get(['id', 'nama_lengkap', 'nis']);
        return response()->json($siswa);
    }

    // 4. LAPORAN HARIAN
    public function laporan(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_kelas = $request->get('id_kelas');
        $role = $request->get('role') ?? 'siswa';

        $query = Presensi::where('role', $role)->where('tanggal', 'like', "$bulan%");

        if ($role === 'siswa') {
            $query->with('siswa.kelas');
            if ($id_kelas) {
                $query->whereHas('siswa', function ($q) use ($id_kelas) {
                    $q->where('kelas_id', $id_kelas);
                });
            }
        } else {
            $query->with('guru');
        }

        $data = $query->orderBy('tanggal', 'desc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Admin/Presensi/Laporan', [
            'data' => $data,
            'kelas' => $kelas,
            'bulan' => $bulan,
            'filter_kelas' => $id_kelas,
            'filter_role' => $role
        ]);
    }

    // 5. REKAP BULANAN (Tgl 19 Bulan Sebelumnya - Tgl 18 Bulan Terpilih)
    public function rekap(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_kelas = $request->get('id_kelas');
        $role = $request->get('role') ?? 'siswa';
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        // Tentukan Rentang Tanggal (19 prev month s/d 18 current month)
        $selected_time = strtotime($bulan . '-01');
        $prev_month_time = strtotime('-1 month', $selected_time);
        
        $start_date = date('Y-m', $prev_month_time) . '-19';
        $end_date = $bulan . '-18';

        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        if ($role === 'siswa' && !$id_kelas) {
            return Inertia::render('Admin/Presensi/Rekap', [
                'kelas' => $kelas, 'bulan' => $bulan, 'filter_kelas' => '', 'filter_role' => $role, 'data_rekap' => [], 'dates' => $dates
            ]);
        }

        if ($role === 'siswa') {
            $pengguna = Siswa::where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get();
        } else {
            $pengguna = Guru::orderBy('nama_lengkap', 'asc')->get();
        }
        
        $presensi = Presensi::where('role', $role)
                            ->whereBetween('tanggal', [$start_date, $end_date])
                            ->get();

        $absen_map = [];
        foreach ($presensi as $p) {
            $absen_map[$p->user_id][$p->tanggal] = [
                'status' => $p->status_kehadiran, 
                'verif' => $p->status_verifikasi,
                'menit' => $p->menit_terlambat ?? 0,
                'jam_masuk' => $p->jam_masuk
            ];
        }

        $libur_array = [];
        $libur_map = [];
        $liburs = \App\Models\HariLibur::whereBetween('tanggal', [$start_date, $end_date])->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = $lbr->tanggal;
            $libur_map[$lbr->tanggal] = $lbr->keterangan;
        }

        $tahun = date('Y', strtotime($bulan . '-01'));
        $libur_nasional = \App\Models\HariLibur::getLiburNasionalMap($tahun);

        $info_libur = [];
        foreach ($dates as $tgl) {
            $kode_hari = date('N', strtotime($tgl));
            if (isset($libur_map[$tgl])) {
                $info_libur[$tgl] = $libur_map[$tgl];
            } elseif (isset($libur_nasional[$tgl])) {
                $info_libur[$tgl] = $libur_nasional[$tgl];
                $libur_array[] = $tgl; // tambahkan agar di $status_final jadi '-' 
            } elseif ($kode_hari == 6 || $kode_hari == 7) {
                $info_libur[$tgl] = 'Libur Akhir Pekan';
            }
        }

        $data_rekap = [];
        $hari_ini_real = date('Y-m-d');

        foreach ($pengguna as $s) {
            $row = [
                'nama' => $s->nama_lengkap ?? $s->nama_guru,
                'identitas' => $role === 'siswa' ? $s->nis : ($s->nik ?? '-'),
                'harian' => [],
                'total' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0, 'DL' => 0, 'C' => 0],
                'total_menit_terlambat' => 0,
                'persen' => 0
            ];

            foreach ($dates as $tgl) {
                $kode_hari = date('N', strtotime($tgl));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_libur_nasional = in_array($tgl, $libur_array);
                $is_future = (strtotime($tgl) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$s->id][$tgl] ?? null;
                $jamSetting = JamSekolah::find(1);
                $mulai_terlambat = $jamSetting->jam_masuk_mulai_terlambat ?? '07:00:00';

                if ($is_weekend || $is_libur_nasional || $is_future) {
                    $status_final = '-';
                } else {
                    if ($data_tgl) {
                        $st_asli = $data_tgl['status'];
                        if (empty($st_asli) || $st_asli == 'Tepat Waktu') $st_asli = 'Hadir';

                        // FIX: Retroactive calculation if previously saved incorrectly as Hadir
                        if ($st_asli == 'Hadir' && !empty($data_tgl['jam_masuk'])) {
                            if ($data_tgl['jam_masuk'] > $mulai_terlambat) {
                                $st_asli = 'Terlambat';
                                $mulai_time = strtotime($mulai_terlambat);
                                $sekarang_time = strtotime($data_tgl['jam_masuk']);
                                $data_tgl['menit'] = floor(($sekarang_time - $mulai_time) / 60);
                                if ($data_tgl['menit'] < 0) $data_tgl['menit'] = 0;
                            }
                        }

                        $verif = $data_tgl['verif'];
                        // Jika Izin/Sakit/Dinas Luar ditolak maka Alfa
                        $status_final = (in_array($st_asli, ['Izin', 'Sakit', 'Dinas Luar', 'Cuti']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
                        if ($status_final == 'Terlambat') {
                            $row['total_menit_terlambat'] += $data_tgl['menit'];
                        }
                    } else {
                        $status_final = 'Alpha';
                    }
                }

                $row['harian'][$tgl] = $status_final;

                if ($status_final == 'Hadir') $row['total']['H']++;
                if ($status_final == 'Terlambat') { $row['total']['T']++; $row['total']['H']++; }
                if ($status_final == 'Sakit') $row['total']['S']++;
                if ($status_final == 'Izin') $row['total']['I']++;
                if ($status_final == 'Alpha') $row['total']['A']++;
                if ($status_final == 'Dinas Luar') $row['total']['DL']++;
                if ($status_final == 'Cuti') $row['total']['C']++;
            }
            // Izin dan Sakit hitung Alfa presentasenya (Hanya Hadir dan DL yang efektif)
            // Cuti juga tidak memotong/menambah hadir tapi bagian dari hitungan? Atau Cuti itu hak, jadi tidak mengurangi persentase
            $total_efektif = $row['total']['H'] + $row['total']['S'] + $row['total']['I'] + $row['total']['A'] + $row['total']['DL'] + $row['total']['C'];
            $total_hadir = $row['total']['H'] + $row['total']['DL'] + $row['total']['C'];
            $row['persen'] = ($total_efektif > 0) ? round(($total_hadir / $total_efektif) * 100) : 0;
            
            // Format menit ke jam & menit
            $jam_t = floor($row['total_menit_terlambat'] / 60);
            $menit_t = $row['total_menit_terlambat'] % 60;
            $row['format_terlambat'] = ($jam_t > 0 ? $jam_t . 'j ' : '') . ($menit_t > 0 || $jam_t == 0 ? $menit_t . 'm' : '');

            $data_rekap[] = $row;
        }

        return Inertia::render('Admin/Presensi/Rekap', [
            'kelas' => $kelas, 'bulan' => $bulan, 'filter_kelas' => $id_kelas, 'filter_role' => $role, 'data_rekap' => $data_rekap, 'dates' => $dates, 'info_libur' => $info_libur
        ]);
    }

    // 6. CETAKAN
    public function cetakHarian(Request $request)
    {
        $tanggal = $request->get('tanggal') ?? date('Y-m-d');
        $id_kelas = $request->get('id_kelas');

        $querySiswa = Siswa::with('kelas')->orderBy('nama_lengkap', 'asc');
        if ($id_kelas) {
            $querySiswa->where('kelas_id', $id_kelas);
        }
        $siswa = $querySiswa->get();

        $presensi = Presensi::where('role', 'siswa')->where('tanggal', $tanggal)->get()->keyBy('user_id');

        $kode_hari = date('N', strtotime($tanggal));
        $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
        $is_future = (strtotime($tanggal) > strtotime(date('Y-m-d')));
        $libur = \App\Models\HariLibur::where('tanggal', $tanggal)->first();
        $is_libur = $is_weekend || $libur;

        $data_bermasalah = [];

        foreach ($siswa as $s) {
            $p = $presensi->get($s->id);

            if ($p) {
                $status = $p->status_kehadiran;
                $verif = $p->status_verifikasi;

                if (in_array($status, ['Izin', 'Sakit']) && $verif !== 'Disetujui') {
                    $status_final = 'Alpha';
                    $ket = 'Ditolak/Belum di-ACC';
                } else {
                    $status_final = $status;
                    $ket = $p->keterangan ?? '-';
                }

                if ($status_final != 'Hadir') {
                    $data_bermasalah[] = [
                        'nama_lengkap' => $s->nama_lengkap,
                        'nama_kelas' => $s->kelas->nama_kelas ?? '-',
                        'status_kehadiran' => $status_final,
                        'keterangan' => $ket
                    ];
                }
            } else {
                if (!$is_libur && !$is_future) {
                    $data_bermasalah[] = [
                        'nama_lengkap' => $s->nama_lengkap,
                        'nama_kelas' => $s->kelas->nama_kelas ?? '-',
                        'status_kehadiran' => 'Alpha',
                        'keterangan' => 'Tanpa Keterangan'
                    ];
                }
            }
        }

        $sekolah = Sekolah::find(1);

        return view('presensi.cetak_harian', [
            'tipe' => 'siswa',
            'data' => $data_bermasalah,
            'tanggal' => $tanggal,
            'sekolah' => $sekolah
        ]);
    }

    public function cetakRekap(Request $request)
    {
        // Similar to rekap logic but returning a view
        $bulan = $request->get('bulan');
        $id_kelas = $request->get('id_kelas');
        if (!$bulan || !$id_kelas) return "Silakan pilih Kelas dan Bulan terlebih dahulu.";

        $kelas = Kelas::find($id_kelas);
        $siswa = Siswa::where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get();
        
        $selected_time = strtotime($bulan . '-01');
        $prev_month_time = strtotime('-1 month', $selected_time);
        
        $start_date = date('Y-m', $prev_month_time) . '-19';
        $end_date = $bulan . '-18';

        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $presensi = Presensi::where('role', 'siswa')->whereBetween('tanggal', [$start_date, $end_date])->get();

        $libur_array = [];
        $libur_map = [];
        $liburs = \App\Models\HariLibur::whereBetween('tanggal', [$start_date, $end_date])->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = $lbr->tanggal;
            $libur_map[$lbr->tanggal] = $lbr->keterangan;
        }

        $tahun = date('Y', strtotime($bulan . '-01'));
        $libur_nasional = \App\Models\HariLibur::getLiburNasionalMap($tahun);

        $info_libur = [];
        foreach ($dates as $tgl) {
            $kode_hari = date('N', strtotime($tgl));
            if (isset($libur_map[$tgl])) {
                $info_libur[$tgl] = $libur_map[$tgl];
            } elseif (isset($libur_nasional[$tgl])) {
                $info_libur[$tgl] = $libur_nasional[$tgl];
                $libur_array[] = $tgl;
            } elseif ($kode_hari == 6 || $kode_hari == 7) {
                $info_libur[$tgl] = 'Libur Akhir Pekan';
            }
        }

        $absen_map = [];
        foreach ($presensi as $p) {
            $absen_map[$p->user_id][$p->tanggal] = ['status' => $p->status_kehadiran, 'verif' => $p->status_verifikasi, 'menit' => $p->menit_terlambat ?? 0, 'jam_masuk' => $p->jam_masuk];
        }

        $data_rekap = [];
        $hari_ini_real = date('Y-m-d');

        foreach ($siswa as $s) {
            $row = ['nama' => $s->nama_lengkap, 'nis' => $s->nis, 'total' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0, 'C' => 0], 'total_menit' => 0];

            foreach ($dates as $tgl) {
                $kode_hari = date('N', strtotime($tgl));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_libur_nasional = in_array($tgl, $libur_array);
                $is_future = (strtotime($tgl) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$s->id][$tgl] ?? null;
                $jamSetting = JamSekolah::find(1);
                $mulai_terlambat = $jamSetting->jam_masuk_mulai_terlambat ?? '07:00:00';

                if ($is_weekend || $is_libur_nasional || $is_future) {
                    $status_final = '-';
                } else {
                    if ($data_tgl) {
                        $st_asli = $data_tgl['status'];
                        if (empty($st_asli) || $st_asli == 'Tepat Waktu') $st_asli = 'Hadir'; // FIX BUG CI4

                        // FIX: Retroactive calculation if previously saved incorrectly as Hadir
                        if ($st_asli == 'Hadir' && !empty($data_tgl['jam_masuk'])) {
                            if ($data_tgl['jam_masuk'] > $mulai_terlambat) {
                                $st_asli = 'Terlambat';
                                $mulai_time = strtotime($mulai_terlambat);
                                $sekarang_time = strtotime($data_tgl['jam_masuk']);
                                $data_tgl['menit'] = floor(($sekarang_time - $mulai_time) / 60);
                                if ($data_tgl['menit'] < 0) $data_tgl['menit'] = 0;
                            }
                        }

                        $verif = $data_tgl['verif'];
                        $status_final = (in_array($st_asli, ['Izin', 'Sakit', 'Dinas Luar']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
                        if ($status_final == 'Terlambat') {
                            $row['total_menit'] += $data_tgl['menit'];
                        }
                    } else {
                        $status_final = 'Alpha';
                    }
                }

                if ($status_final == 'Hadir') $row['total']['H']++;
                if ($status_final == 'Terlambat') { $row['total']['T']++; $row['total']['H']++; }
                if ($status_final == 'Sakit') $row['total']['S']++;
                if ($status_final == 'Izin') $row['total']['I']++;
                if ($status_final == 'Alpha') $row['total']['A']++;
                if ($status_final == 'Cuti') $row['total']['C']++;
            }
            
            $jam_t = floor($row['total_menit'] / 60);
            $menit_t = $row['total_menit'] % 60;
            $row['format_terlambat'] = ($jam_t > 0 ? $jam_t . 'j ' : '') . ($menit_t > 0 || $jam_t == 0 ? $menit_t . 'm' : '');
            
            $data_rekap[] = $row;
        }

        return view('presensi.cetak_rekap', [
            'tipe' => 'siswa', 'data_rekap' => $data_rekap, 'kelas' => $kelas, 'bulan' => $bulan, 'sekolah' => Sekolah::find(1), 'dates' => $dates, 'info_libur' => $info_libur
        ]);
    }

    public function cetakMatrix(Request $request)
    {
        $bulanStr = $request->get('bulan');
        $id_kelas = $request->get('id_kelas');
        if (empty($bulanStr) || empty($id_kelas)) return redirect()->back()->with('error', 'Pilih Bulan dan Kelas!');

        $kelas = Kelas::find($id_kelas);
        $siswa = Siswa::where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get();
        
        $selected_time = strtotime($bulanStr . '-01');
        $prev_month_time = strtotime('-1 month', $selected_time);
        
        $start_date = date('Y-m', $prev_month_time) . '-19';
        $end_date = $bulanStr . '-18';

        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $absensi = Presensi::where('role', 'siswa')->whereBetween('tanggal', [$start_date, $end_date])->get();

        $absen_map = [];
        foreach ($absensi as $row) {
            $absen_map[$row->user_id][$row->tanggal] = ['status' => $row->status_kehadiran, 'verif' => $row->status_verifikasi, 'menit' => $row->menit_terlambat ?? 0, 'jam_masuk' => $row->jam_masuk];
        }

        $libur_array = [];
        $libur_map = [];
        $liburs = \App\Models\HariLibur::whereBetween('tanggal', [$start_date, $end_date])->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = $lbr->tanggal;
            $libur_map[$lbr->tanggal] = $lbr->keterangan;
        }

        $tahun = date('Y', strtotime($bulanStr . '-01'));
        $libur_nasional = \App\Models\HariLibur::getLiburNasionalMap($tahun);

        $info_libur = [];
        foreach ($dates as $tgl) {
            $kode_hari = date('N', strtotime($tgl));
            if (isset($libur_map[$tgl])) {
                $info_libur[$tgl] = $libur_map[$tgl];
            } elseif (isset($libur_nasional[$tgl])) {
                $info_libur[$tgl] = $libur_nasional[$tgl];
                $libur_array[] = $tgl;
            } elseif ($kode_hari == 6 || $kode_hari == 7) {
                $info_libur[$tgl] = 'Libur Akhir Pekan';
            }
        }

        $hari_ini_real = date('Y-m-d');
        $data_matrix = [];
        $data_menit = [];

        foreach ($siswa as $s) {
            $data_menit[$s->id] = 0;
            foreach ($dates as $tgl) {
                $kode_hari = date('N', strtotime($tgl));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_future = (strtotime($tgl) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$s->id][$tgl] ?? null;
                $jamSetting = JamSekolah::find(1);
                $mulai_terlambat = $jamSetting->jam_masuk_mulai_terlambat ?? '07:00:00';

                $is_libur_nasional = in_array($tgl, $libur_array);

                if ($is_weekend || $is_libur_nasional || $is_future) {
                    $data_matrix[$s->id][$tgl] = '-';
                } else {
                    if ($data_tgl) {
                        $st_asli = $data_tgl['status'];
                        if (empty($st_asli) || $st_asli == 'Tepat Waktu') $st_asli = 'Hadir';

                        // FIX: Retroactive calculation if previously saved incorrectly as Hadir
                        if ($st_asli == 'Hadir' && !empty($data_tgl['jam_masuk'])) {
                            if ($data_tgl['jam_masuk'] > $mulai_terlambat) {
                                $st_asli = 'Terlambat';
                                $mulai_time = strtotime($mulai_terlambat);
                                $sekarang_time = strtotime($data_tgl['jam_masuk']);
                                $data_tgl['menit'] = floor(($sekarang_time - $mulai_time) / 60);
                                if ($data_tgl['menit'] < 0) $data_tgl['menit'] = 0;
                            }
                        }

                        $verif = $data_tgl['verif'];

                        $status = 'A';
                        if ($st_asli == 'Hadir') $status = 'H';
                        if ($st_asli == 'Terlambat') { $status = 'T'; $data_menit[$s->id] += $data_tgl['menit']; }
                        if ($st_asli == 'Sakit' && $verif == 'Disetujui') $status = 'S';
                        if ($st_asli == 'Izin' && $verif == 'Disetujui') $status = 'I';
                        if ($st_asli == 'Cuti' && $verif == 'Disetujui') $status = 'C';

                        $data_matrix[$s->id][$tgl] = $status;
                    } else {
                        $data_matrix[$s->id][$tgl] = 'A';
                    }
                }
            }
        }

        return view('presensi.cetak_matrix', [
            'tipe' => 'siswa', 'siswa' => $siswa, 'matrix' => $data_matrix, 'dates' => $dates, 'data_menit' => $data_menit, 'bulan' => $bulanStr, 'kelas' => $kelas, 'sekolah' => Sekolah::find(1), 'info_libur' => $info_libur
        ]);
    }

    public function cetakHarianGuru(Request $request)
    {
        $tanggal = $request->get('tanggal') ?? date('Y-m-d');

        $guru = Guru::orderBy('nama_lengkap', 'asc')->get();
        $presensi = Presensi::where('role', 'guru')->where('tanggal', $tanggal)->get()->keyBy('user_id');

        $kode_hari = date('N', strtotime($tanggal));
        $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
        $is_future = (strtotime($tanggal) > strtotime(date('Y-m-d')));
        $is_libur = $is_weekend;

        $data_bermasalah = [];

        foreach ($guru as $g) {
            $p = $presensi->get($g->id);

            if ($p) {
                $status = $p->status_kehadiran;
                $verif = $p->status_verifikasi;

                if (in_array($status, ['Izin', 'Sakit', 'Dinas Luar']) && $verif !== 'Disetujui') {
                    $status_final = 'Alpha';
                    $ket = 'Ditolak/Belum di-ACC';
                } else {
                    $status_final = $status;
                    $ket = $p->keterangan ?? '-';
                }

                if ($status_final != 'Hadir') {
                    $data_bermasalah[] = [
                        'nama_lengkap' => $g->nama_lengkap,
                        'nama_kelas' => 'GURU & STAFF',
                        'status_kehadiran' => $status_final,
                        'keterangan' => $ket
                    ];
                }
            } else {
                if (!$is_libur && !$is_future) {
                    $data_bermasalah[] = [
                        'nama_lengkap' => $g->nama_lengkap,
                        'nama_kelas' => 'GURU & STAFF',
                        'status_kehadiran' => 'Alpha',
                        'keterangan' => 'Tanpa Keterangan'
                    ];
                }
            }
        }

        return view('presensi.cetak_harian', [
            'tipe' => 'guru',
            'data' => $data_bermasalah,
            'tanggal' => $tanggal,
            'sekolah' => Sekolah::find(1)
        ]);
    }

    public function cetakMatrixGuru(Request $request)
    {
        $bulanStr = $request->get('bulan');
        if (empty($bulanStr)) return redirect()->back()->with('error', 'Pilih Bulan terlebih dahulu!');

        $guru = Guru::orderBy('nama_lengkap', 'asc')->get();
        
        $selected_time = strtotime($bulanStr . '-01');
        $prev_month_time = strtotime('-1 month', $selected_time);
        
        $start_date = date('Y-m', $prev_month_time) . '-19';
        $end_date = $bulanStr . '-18';

        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }
        
        $absensi = Presensi::where('role', 'guru')->whereBetween('tanggal', [$start_date, $end_date])->get();

        $absen_map = [];
        foreach ($absensi as $row) {
            $absen_map[$row->user_id][$row->tanggal] = ['status' => $row->status_kehadiran, 'verif' => $row->status_verifikasi, 'menit' => $row->menit_terlambat ?? 0, 'jam_masuk' => $row->jam_masuk];
        }

        $libur_array = [];
        $libur_map = [];
        $liburs = \App\Models\HariLibur::whereBetween('tanggal', [$start_date, $end_date])->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = $lbr->tanggal;
            $libur_map[$lbr->tanggal] = $lbr->keterangan;
        }

        $tahun = date('Y', strtotime($bulanStr . '-01'));
        $libur_nasional = \App\Models\HariLibur::getLiburNasionalMap($tahun);

        $info_libur = [];
        foreach ($dates as $tgl) {
            $kode_hari = date('N', strtotime($tgl));
            if (isset($libur_map[$tgl])) {
                $info_libur[$tgl] = $libur_map[$tgl];
            } elseif (isset($libur_nasional[$tgl])) {
                $info_libur[$tgl] = $libur_nasional[$tgl];
                $libur_array[] = $tgl;
            } elseif ($kode_hari == 6 || $kode_hari == 7) {
                $info_libur[$tgl] = 'Libur Akhir Pekan';
            }
        }

        $hari_ini_real = date('Y-m-d');
        $data_matrix = [];
        $data_menit = [];

        foreach ($guru as $g) {
            $data_menit[$g->id] = 0;
            foreach ($dates as $tgl) {
                $kode_hari = date('N', strtotime($tgl));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_future = (strtotime($tgl) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$g->id][$tgl] ?? null;
                $jamSetting = JamSekolah::find(1);
                $mulai_terlambat = $jamSetting->jam_masuk_mulai_terlambat ?? '07:00:00';

                $is_libur_nasional = in_array($tgl, $libur_array);

                if ($is_weekend || $is_libur_nasional || $is_future) {
                    $data_matrix[$g->id][$tgl] = '-';
                } else {
                    if ($data_tgl) {
                        $st_asli = $data_tgl['status'];
                        if (empty($st_asli) || $st_asli == 'Tepat Waktu') $st_asli = 'Hadir';

                        // FIX: Retroactive calculation if previously saved incorrectly as Hadir
                        if ($st_asli == 'Hadir' && !empty($data_tgl['jam_masuk'])) {
                            if ($data_tgl['jam_masuk'] > $mulai_terlambat) {
                                $st_asli = 'Terlambat';
                                $mulai_time = strtotime($mulai_terlambat);
                                $sekarang_time = strtotime($data_tgl['jam_masuk']);
                                $data_tgl['menit'] = floor(($sekarang_time - $mulai_time) / 60);
                                if ($data_tgl['menit'] < 0) $data_tgl['menit'] = 0;
                            }
                        }

                        $verif = $data_tgl['verif'];

                        $status = 'A';
                        if ($st_asli == 'Hadir') $status = 'H';
                        if ($st_asli == 'Terlambat') { $status = 'T'; $data_menit[$g->id] += $data_tgl['menit']; }
                        if ($st_asli == 'Sakit' && $verif == 'Disetujui') $status = 'S';
                        if ($st_asli == 'Izin' && $verif == 'Disetujui') $status = 'I';
                        if ($st_asli == 'Dinas Luar' && $verif == 'Disetujui') $status = 'DL';
                        if ($st_asli == 'Cuti' && $verif == 'Disetujui') $status = 'C';

                        $data_matrix[$g->id][$tgl] = $status;
                    } else {
                        $data_matrix[$g->id][$tgl] = 'A';
                    }
                }
            }
        }

        return view('presensi.cetak_matrix', [
            'tipe' => 'guru', 'siswa' => $guru, 'matrix' => $data_matrix, 'dates' => $dates, 'data_menit' => $data_menit, 'bulan' => $bulanStr, 'kelas' => (object)['nama_kelas'=>'GURU & STAFF'], 'sekolah' => Sekolah::find(1), 'info_libur' => $info_libur
        ]);
    }
}
