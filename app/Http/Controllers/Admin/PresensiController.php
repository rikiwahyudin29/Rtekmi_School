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

        // C. CEK PRESENSI
        $cekAbsen = Presensi::where('user_id', $userID)
                            ->where('role', $role)
                            ->where('tanggal', $tgl_hari_ini)
                            ->first();

        if (!$cekAbsen) {
            // MASUK
            $batas_masuk = $jamSetting->jam_masuk_akhir;
            $status = ($jam_sekarang > $batas_masuk) ? 'Terlambat' : 'Hadir';

            Presensi::create([
                'user_id' => $userID,
                'role' => $role,
                'tanggal' => $tgl_hari_ini,
                'jam_masuk' => $jam_sekarang,
                'status_kehadiran' => $status,
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
            ->whereIn('status_kehadiran', ['Izin', 'Sakit'])
            ->orderBy('created_at', 'desc')
            ->get();

        $izinGuru = Presensi::with('guru')
            ->where('role', 'guru')
            ->whereIn('status_kehadiran', ['Izin', 'Sakit', 'Dinas Luar'])
            ->orderBy('created_at', 'desc')
            ->get();

        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Admin/Presensi/Izin', [
            'izinSiswa' => $izinSiswa,
            'izinGuru' => $izinGuru,
            'tanggal' => $tanggal,
            'kelas' => $kelas
        ]);
    }

    public function simpanIzin(Request $request)
    {
        $id_siswa = $request->input('id_siswa');
        $status = $request->input('status');
        $keterangan = $request->input('keterangan');
        $tanggal = $request->input('tanggal') ?? date('Y-m-d');

        $namaFile = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/surat_izin'), $namaFile);
        }

        $cek = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', $tanggal)
            ->count();

        if ($cek > 0) {
            return redirect()->back()->with('error', 'Siswa ini sudah memiliki presensi pada hari tersebut.');
        }

        Presensi::create([
            'user_id' => $id_siswa,
            'role' => 'siswa',
            'tanggal' => $tanggal,
            'jam_masuk' => date('H:i:s'),
            'status_kehadiran' => $status,
            'metode' => 'Manual',
            'status_verifikasi' => 'Disetujui',
            'keterangan' => $keterangan,
            'bukti_izin' => $namaFile
        ]);

        $siswa = Siswa::find($id_siswa);
        if ($siswa && !empty($siswa->no_hp_siswa)) {
            $pesan = "📢 *INFO PRESENSI*\n\nStatus siswa a.n *$siswa->nama_lengkap* hari ini tercatat: *$status*.\n📝 Ket: $keterangan\n\n_Terima kasih._";
            $this->wa->kirim($siswa->no_hp_siswa, $pesan);
        }

        return redirect()->back()->with('success', 'Data Izin/Sakit manual berhasil ditambahkan.');
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

    // 5. REKAP BULANAN
    public function rekap(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_kelas = $request->get('id_kelas');
        $role = $request->get('role') ?? 'siswa';
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        if ($role === 'siswa' && !$id_kelas) {
            return Inertia::render('Admin/Presensi/Rekap', [
                'kelas' => $kelas, 'bulan' => $bulan, 'filter_kelas' => '', 'filter_role' => $role, 'data_rekap' => [], 'jml_hari' => date('t', strtotime($bulan))
            ]);
        }

        if ($role === 'siswa') {
            $pengguna = Siswa::where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get();
        } else {
            $pengguna = Guru::orderBy('nama_lengkap', 'asc')->get();
        }
        
        $presensi = Presensi::where('role', $role)
                            ->where('tanggal', 'like', "$bulan%")
                            ->get();

        $absen_map = [];
        foreach ($presensi as $p) {
            $tgl = (int) date('d', strtotime($p->tanggal));
            $absen_map[$p->user_id][$tgl] = ['status' => $p->status_kehadiran, 'verif' => $p->status_verifikasi];
        }

        $libur_array = [];
        // TODO: Handle hari libur if table exists
        $liburs = \App\Models\HariLibur::where('tanggal', 'like', "$bulan%")->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = (int) date('d', strtotime($lbr->tanggal));
        }

        $data_rekap = [];
        $jumlah_hari = date('t', strtotime($bulan));
        $hari_ini_real = date('Y-m-d');

        foreach ($pengguna as $s) {
            $row = [
                'nama' => $s->nama_lengkap ?? $s->nama_guru,
                'identitas' => $role === 'siswa' ? $s->nis : ($s->nik ?? '-'),
                'harian' => [],
                'total' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0, 'DL' => 0],
                'persen' => 0
            ];

            for ($d = 1; $d <= $jumlah_hari; $d++) {
                $tanggal_loop = $bulan . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                $kode_hari = date('N', strtotime($tanggal_loop));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_libur_nasional = in_array($d, $libur_array);
                $is_future = (strtotime($tanggal_loop) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$s->id][$d] ?? null;

                if ($data_tgl) {
                    $st_asli = $data_tgl['status'];
                    $verif = $data_tgl['verif'];
                    $status_final = (in_array($st_asli, ['Izin', 'Sakit', 'Dinas Luar']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
                } else {
                    $status_final = ($is_weekend || $is_libur_nasional || $is_future) ? '-' : 'Alpha';
                }

                $row['harian'][$d] = $status_final;

                if ($status_final == 'Hadir') $row['total']['H']++;
                if ($status_final == 'Terlambat') { $row['total']['T']++; $row['total']['H']++; }
                if ($status_final == 'Sakit') $row['total']['S']++;
                if ($status_final == 'Izin') $row['total']['I']++;
                if ($status_final == 'Alpha') $row['total']['A']++;
                if ($status_final == 'Dinas Luar') $row['total']['DL']++;
            }
            $total_efektif = $row['total']['H'] + $row['total']['S'] + $row['total']['I'] + $row['total']['A'] + $row['total']['DL'];
            $row['persen'] = ($total_efektif > 0) ? round(($row['total']['H'] / $total_efektif) * 100) : 0;
            $data_rekap[] = $row;
        }

        return Inertia::render('Admin/Presensi/Rekap', [
            'kelas' => $kelas, 'bulan' => $bulan, 'filter_kelas' => $id_kelas, 'filter_role' => $role, 'data_rekap' => $data_rekap, 'jml_hari' => $jumlah_hari
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
        $is_libur = $is_weekend; // Tambahkan cek libur nasional jika perlu

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
        $presensi = Presensi::where('role', 'siswa')->where('tanggal', 'like', "$bulan%")->get();

        $absen_map = [];
        foreach ($presensi as $p) {
            $tgl = (int) date('d', strtotime($p->tanggal));
            $absen_map[$p->user_id][$tgl] = ['status' => $p->status_kehadiran, 'verif' => $p->status_verifikasi];
        }

        $data_rekap = [];
        $jumlah_hari = date('t', strtotime($bulan));
        $hari_ini_real = date('Y-m-d');

        foreach ($siswa as $s) {
            $row = ['nama' => $s->nama_lengkap, 'nis' => $s->nis, 'total' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0]];

            for ($d = 1; $d <= $jumlah_hari; $d++) {
                $tanggal_loop = $bulan . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                $kode_hari = date('N', strtotime($tanggal_loop));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_future = (strtotime($tanggal_loop) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$s->id][$d] ?? null;

                if ($data_tgl) {
                    $st_asli = $data_tgl['status'];
                    $verif = $data_tgl['verif'];
                    $status_final = (in_array($st_asli, ['Izin', 'Sakit']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
                } else {
                    $status_final = ($is_weekend || $is_future) ? '-' : 'Alpha';
                }

                if ($status_final == 'Hadir') $row['total']['H']++;
                if ($status_final == 'Terlambat') { $row['total']['T']++; $row['total']['H']++; }
                if ($status_final == 'Sakit') $row['total']['S']++;
                if ($status_final == 'Izin') $row['total']['I']++;
                if ($status_final == 'Alpha') $row['total']['A']++;
            }
            $data_rekap[] = $row;
        }

        return view('presensi.cetak_rekap', [
            'tipe' => 'siswa', 'data_rekap' => $data_rekap, 'kelas' => $kelas, 'bulan' => $bulan, 'jml_hari' => $jumlah_hari, 'sekolah' => Sekolah::find(1)
        ]);
    }

    public function cetakMatrix(Request $request)
    {
        // ... (We will use the similar rekap logic for the matrix view)
        $bulanStr = $request->get('bulan');
        $id_kelas = $request->get('id_kelas');
        if (empty($bulanStr) || empty($id_kelas)) return redirect()->back()->with('error', 'Pilih Bulan dan Kelas!');

        $kelas = Kelas::find($id_kelas);
        $siswa = Siswa::where('kelas_id', $id_kelas)->orderBy('nama_lengkap', 'asc')->get();
        $absensi = Presensi::where('role', 'siswa')->where('tanggal', 'like', "$bulanStr%")->get();

        $absen_map = [];
        foreach ($absensi as $row) {
            $tgl = (int) date('d', strtotime($row->tanggal));
            $absen_map[$row->user_id][$tgl] = ['status' => $row->status_kehadiran, 'verif' => $row->status_verifikasi];
        }

        $jml_hari = date('t', strtotime($bulanStr));
        $hari_ini_real = date('Y-m-d');
        $data_matrix = [];

        foreach ($siswa as $s) {
            for ($d = 1; $d <= $jml_hari; $d++) {
                $tanggal_loop = $bulanStr . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                $kode_hari = date('N', strtotime($tanggal_loop));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_future = (strtotime($tanggal_loop) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$s->id][$d] ?? null;

                if ($data_tgl) {
                    $st_asli = $data_tgl['status'];
                    $verif = $data_tgl['verif'];

                    $status = 'A';
                    if ($st_asli == 'Hadir') $status = 'H';
                    if ($st_asli == 'Terlambat') $status = 'T';
                    if ($st_asli == 'Sakit' && $verif == 'Disetujui') $status = 'S';
                    if ($st_asli == 'Izin' && $verif == 'Disetujui') $status = 'I';

                    $data_matrix[$s->id][$d] = $status;
                } else {
                    $data_matrix[$s->id][$d] = ($is_weekend || $is_future) ? '-' : 'A';
                }
            }
        }

        return view('presensi.cetak_matrix', [
            'tipe' => 'siswa', 'siswa' => $siswa, 'matrix' => $data_matrix, 'jml_hari' => $jml_hari, 'bulan' => $bulanStr, 'kelas' => $kelas, 'sekolah' => Sekolah::find(1)
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
        $absensi = Presensi::where('role', 'guru')->where('tanggal', 'like', "$bulanStr%")->get();

        $absen_map = [];
        foreach ($absensi as $row) {
            $tgl = (int) date('d', strtotime($row->tanggal));
            $absen_map[$row->user_id][$tgl] = ['status' => $row->status_kehadiran, 'verif' => $row->status_verifikasi];
        }

        $jml_hari = date('t', strtotime($bulanStr));
        $hari_ini_real = date('Y-m-d');
        $data_matrix = [];

        foreach ($guru as $g) {
            for ($d = 1; $d <= $jml_hari; $d++) {
                $tanggal_loop = $bulanStr . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                $kode_hari = date('N', strtotime($tanggal_loop));
                $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
                $is_future = (strtotime($tanggal_loop) > strtotime($hari_ini_real));

                $data_tgl = $absen_map[$g->id][$d] ?? null;

                if ($data_tgl) {
                    $st_asli = $data_tgl['status'];
                    $verif = $data_tgl['verif'];

                    $status = 'A';
                    if ($st_asli == 'Hadir') $status = 'H';
                    if ($st_asli == 'Terlambat') $status = 'T';
                    if ($st_asli == 'Sakit' && $verif == 'Disetujui') $status = 'S';
                    if ($st_asli == 'Izin' && $verif == 'Disetujui') $status = 'I';
                    if ($st_asli == 'Dinas Luar' && $verif == 'Disetujui') $status = 'DL';

                    $data_matrix[$g->id][$d] = $status;
                } else {
                    $data_matrix[$g->id][$d] = ($is_weekend || $is_future) ? '-' : 'A';
                }
            }
        }

        return view('presensi.cetak_matrix', [
            'tipe' => 'guru', 'siswa' => $guru, 'matrix' => $data_matrix, 'jml_hari' => $jml_hari, 'bulan' => $bulanStr, 'kelas' => (object)['nama_kelas'=>'GURU & STAFF'], 'sekolah' => Sekolah::find(1)
        ]);
    }
}
