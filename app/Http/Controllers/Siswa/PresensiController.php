<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JamSekolah;
use App\Models\Siswa;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresensiController extends Controller
{
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c);
    }

    public function index(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_siswa = Auth::user()->siswa->id ?? 0;

        $data = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', 'like', "$bulan%")
            ->orderBy('tanggal', 'desc')
            ->get();

        foreach ($data as $d) {
            if (in_array($d->status_kehadiran, ['Izin', 'Sakit']) && $d->status_verifikasi !== 'Disetujui') {
                $d->display_status = 'Alpha (Menunggu Verifikasi)';
            } else {
                $d->display_status = $d->status_kehadiran;
            }
        }

        return Inertia::render('Siswa/Presensi/Index', [
            'data' => $data,
            'bulan' => $bulan
        ]);
    }

    public function absenHarian()
    {
        $setting = JamSekolah::find(1);
        $id_siswa = Auth::user()->siswa->id ?? 0;

        if (!$setting) {
            return redirect()->route('siswa.dashboard')->with('error', 'Settingan Lokasi Sekolah belum diatur Admin!');
        }

        $hari_ini = date('Y-m-d');
        $sudah_absen = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', $hari_ini)
            ->first();

        return Inertia::render('Siswa/Presensi/AbsenGeo', [
            'lokasi' => $setting,
            'sudah_absen' => $sudah_absen
        ]);
    }

    public function submitAbsen(Request $request)
    {
        $id_siswa = Auth::user()->siswa->id ?? 0;
        $today = date('Y-m-d');
        $now = date('H:i:s');

        $lat_user = $request->input('latitude');
        $long_user = $request->input('longitude');
        $qr_token = $request->input('qr_token');
        $setting = JamSekolah::find(1);

        if ($qr_token !== ($setting->qr_token ?? '')) {
            return redirect()->route('siswa.presensi.absen_harian')->with('error', 'QR Code tidak valid.');
        }

        $jarak_meter = $this->hitungJarak($setting->latitude, $setting->longitude, $lat_user, $long_user);
        if ($jarak_meter > $setting->radius) {
            return redirect()->route('siswa.presensi.absen_harian')->with('error', "Gagal! Kamu di luar radius sekolah.");
        }

        $cek = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', $today)
            ->first();

        if (!$cek) {
            if ($now < $setting->jam_masuk_mulai) {
                return redirect()->route('siswa.presensi.absen_harian')->with('error', "Absen Masuk Belum Dibuka! (Jam: $setting->jam_masuk_mulai)");
            }
            if ($now > $setting->jam_masuk_akhir) {
                return redirect()->route('siswa.presensi.absen_harian')->with('error', "Absen Masuk Sudah Ditutup! (Batas: $setting->jam_masuk_akhir)");
            }

            Presensi::create([
                'user_id' => $id_siswa, 'role' => 'siswa', 'tanggal' => $today, 'jam_masuk' => $now,
                'latitude' => $lat_user, 'longitude' => $long_user, 'jarak_meter' => $jarak_meter,
                'bukti_izin' => 'QR_CODE_SCAN', 'status_kehadiran' => 'Hadir', 'metode' => 'Online', 'status_verifikasi' => 'Disetujui'
            ]);
            return redirect()->route('siswa.presensi.index')->with('success', "✅ Absen Masuk Berhasil!");
        } else {
            if (!empty($cek->jam_pulang)) {
                return redirect()->route('siswa.presensi.absen_harian')->with('error', "Kamu sudah absen pulang.");
            }
            if ($now < $setting->jam_pulang_mulai) {
                return redirect()->route('siswa.presensi.absen_harian')->with('error', "Belum Waktunya Pulang! (Buka Jam: $setting->jam_pulang_mulai)");
            }

            $cek->update(['jam_pulang' => $now]);
            return redirect()->route('siswa.presensi.index')->with('success', "✅ Absen Pulang Berhasil! Hati-hati di jalan.");
        }
    }

    public function izin()
    {
        $id_siswa = Auth::user()->siswa->id ?? 0;
        $riwayat = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->whereIn('status_kehadiran', ['Izin', 'Sakit'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return Inertia::render('Siswa/Presensi/Izin', [
            'riwayat' => $riwayat
        ]);
    }

    public function ajukan(Request $request)
    {
        $id_siswa = Auth::user()->siswa->id ?? 0;

        $namaFile = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/surat_izin'), $namaFile);
        }

        $tanggal = $request->input('tanggal');

        $cek = Presensi::where('user_id', $id_siswa)
            ->where('tanggal', $tanggal)
            ->count();

        if ($cek > 0) {
            return redirect()->route('siswa.presensi.izin')->with('error', 'Anda sudah tercatat presensi pada tanggal tersebut.');
        }

        Presensi::create([
            'user_id' => $id_siswa,
            'role' => 'siswa',
            'tanggal' => $tanggal,
            'status_kehadiran' => $request->input('status'),
            'keterangan' => $request->input('keterangan'),
            'bukti_izin' => $namaFile,
            'metode' => 'Online',
            'status_verifikasi' => 'Pending'
        ]);

        return redirect()->route('siswa.presensi.izin')->with('success', 'Pengajuan berhasil dikirim. Status Anda masih dianggap Alpha sampai disetujui Admin.');
    }

    public function rekap(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_siswa = Auth::user()->siswa->id ?? 0;
        $jml_hari = date('t', strtotime($bulan));

        $absen = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', 'like', "$bulan%")
            ->get();

        $absen_map = [];
        foreach ($absen as $a) {
            $tgl = (int) date('d', strtotime($a->tanggal));
            $absen_map[$tgl] = [
                'status' => $a->status_kehadiran,
                'verif' => $a->status_verifikasi
            ];
        }

        $libur_array = [];
        // TODO: Handle hari libur if table exists

        $map = [];
        $total = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0];
        $hari_ini_real = date('Y-m-d');

        for ($d = 1; $d <= $jml_hari; $d++) {
            $tanggal_loop = $bulan . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            $kode_hari = date('N', strtotime($tanggal_loop));

            $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
            $is_libur_nasional = in_array($d, $libur_array);
            $is_future = (strtotime($tanggal_loop) > strtotime($hari_ini_real));

            $data_tgl = $absen_map[$d] ?? null;

            if ($data_tgl) {
                $st_asli = $data_tgl['status'];
                $verif = $data_tgl['verif'];
                $st_final = (in_array($st_asli, ['Izin', 'Sakit']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
            } else {
                $st_final = ($is_weekend || $is_libur_nasional || $is_future) ? '-' : 'Alpha';
            }

            $map[$d] = $st_final;

            if ($st_final == 'Hadir') $total['H']++;
            if ($st_final == 'Terlambat') { $total['T']++; $total['H']++; }
            if ($st_final == 'Sakit') $total['S']++;
            if ($st_final == 'Izin') $total['I']++;
            if ($st_final == 'Alpha') $total['A']++;
        }

        return Inertia::render('Siswa/Presensi/Rekap', [
            'bulan' => $bulan,
            'map' => $map,
            'total' => $total,
            'jml_hari' => $jml_hari
        ]);
    }

    public function cetakRekap(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_siswa = Auth::user()->siswa->id ?? 0;
        $jml_hari = date('t', strtotime($bulan));

        $siswa = Siswa::with('kelas')->find($id_siswa);

        $absen = Presensi::where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', 'like', "$bulan%")
            ->get();

        $absen_map = [];
        foreach ($absen as $a) {
            $tgl = (int) date('d', strtotime($a->tanggal));
            $absen_map[$tgl] = [
                'status' => $a->status_kehadiran,
                'verif' => $a->status_verifikasi
            ];
        }

        $libur_array = [];

        $map = [];
        $total = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0];
        $hari_ini_real = date('Y-m-d');

        for ($d = 1; $d <= $jml_hari; $d++) {
            $tanggal_loop = $bulan . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            $kode_hari = date('N', strtotime($tanggal_loop));

            $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
            $is_libur_nasional = in_array($d, $libur_array);
            $is_future = (strtotime($tanggal_loop) > strtotime($hari_ini_real));

            $data_tgl = $absen_map[$d] ?? null;

            if ($data_tgl) {
                $st_asli = $data_tgl['status'];
                $verif = $data_tgl['verif'];
                $st_final = (in_array($st_asli, ['Izin', 'Sakit']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
            } else {
                $st_final = ($is_weekend || $is_libur_nasional || $is_future) ? '-' : 'Alpha';
            }

            $map[$d] = $st_final;

            if ($st_final == 'Hadir') $total['H']++;
            if ($st_final == 'Terlambat') { $total['T']++; $total['H']++; }
            if ($st_final == 'Sakit') $total['S']++;
            if ($st_final == 'Izin') $total['I']++;
            if ($st_final == 'Alpha') $total['A']++;
        }

        return view('presensi.cetak_rekap_siswa', [
            'siswa' => $siswa,
            'bulan' => $bulan,
            'map' => $map,
            'total' => $total,
            'jml_hari' => $jml_hari,
            'sekolah' => Sekolah::find(1)
        ]);
    }
}
