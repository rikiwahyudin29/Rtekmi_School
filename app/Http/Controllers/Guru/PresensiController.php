<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\JamSekolah;
use App\Models\Guru;
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
        $id_guru = Auth::user()->guru->id ?? 0;

        $data = Presensi::where('user_id', $id_guru)
            ->where('role', 'guru')
            ->where('tanggal', 'like', "$bulan%")
            ->orderBy('tanggal', 'desc')
            ->get();

        return Inertia::render('Guru/Presensi/Index', [
            'data' => $data,
            'bulan' => $bulan
        ]);
    }

    public function absenHarian()
    {
        $setting = JamSekolah::find(1);
        $id_guru = Auth::user()->guru->id ?? 0;

        if (!$setting) {
            return redirect()->route('guru.dashboard')->with('error', 'Settingan Lokasi Sekolah belum diatur Admin!');
        }

        $hari_ini = date('Y-m-d');
        $sudah_absen = Presensi::where('user_id', $id_guru)
            ->where('role', 'guru')
            ->where('tanggal', $hari_ini)
            ->first();

        return Inertia::render('Guru/Presensi/AbsenGeo', [
            'lokasi' => $setting,
            'sudah_absen' => $sudah_absen
        ]);
    }

    public function submitAbsen(Request $request)
    {
        $id_guru = Auth::user()->guru->id ?? 0;
        $today = date('Y-m-d');
        $now = date('H:i:s');

        $lat_user = $request->input('latitude');
        $long_user = $request->input('longitude');
        $qr_token = $request->input('qr_token');
        $setting = JamSekolah::find(1);

        if ($qr_token !== ($setting->qr_token ?? '')) {
            return redirect()->route('guru.presensi.absen_harian')->with('error', 'Gagal! QR Code tidak valid atau sudah kedaluwarsa.');
        }

        $jarak_meter = $this->hitungJarak($setting->latitude, $setting->longitude, $lat_user, $long_user);
        if ($jarak_meter > $setting->radius) {
            return redirect()->route('guru.presensi.absen_harian')->with('error', "Gagal! Anda di luar radius ($jarak_meter meter).");
        }

        // CEK HARI LIBUR & WEEKEND
        $kode_hari = date('N', strtotime($today));
        $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
        $libur = \App\Models\HariLibur::where('tanggal', $today)->first();

        if ($is_weekend || $libur) {
            return redirect()->route('guru.presensi.absen_harian')->with('error', 'Hari ini libur (' . ($libur ? $libur->keterangan : 'Akhir Pekan') . '). Presensi ditutup!');
        }

        $cek = Presensi::where('user_id', $id_guru)
            ->where('role', 'guru')
            ->where('tanggal', $today)
            ->first();

        if (!$cek) {
            if ($now < $setting->jam_masuk_mulai) {
                return redirect()->route('guru.presensi.absen_harian')->with('error', "Absen Masuk Belum Dibuka! (Buka Jam: $setting->jam_masuk_mulai)");
            }
            if ($now > $setting->jam_masuk_akhir) {
                return redirect()->route('guru.presensi.absen_harian')->with('error', "Absen Masuk Sudah Ditutup! (Batas Akhir: $setting->jam_masuk_akhir)");
            }

            Presensi::create([
                'user_id' => $id_guru, 'role' => 'guru', 'tanggal' => $today, 'jam_masuk' => $now,
                'latitude' => $lat_user, 'longitude' => $long_user, 'jarak_meter' => $jarak_meter,
                'bukti_izin' => 'QR_CODE_SCAN', 'status_kehadiran' => 'Hadir', 'metode' => 'QRCode/Geo', 'status_verifikasi' => 'Terverifikasi'
            ]);
            return redirect()->route('guru.presensi.index')->with('success', "✅ Berhasil Absen Masuk Jam $now");
        } else {
            if (!empty($cek->jam_pulang)) {
                return redirect()->route('guru.presensi.absen_harian')->with('error', "Anda sudah melakukan absen pulang hari ini.");
            }
            if ($now < $setting->jam_pulang_mulai) {
                return redirect()->route('guru.presensi.absen_harian')->with('error', "Belum Waktunya Pulang Bosku! (Absen Pulang Buka Jam: $setting->jam_pulang_mulai)");
            }

            $cek->update(['jam_pulang' => $now]);
            return redirect()->route('guru.presensi.index')->with('success', "✅ Berhasil Absen Pulang Jam $now. Hati-hati di jalan!");
        }
    }

    public function izin()
    {
        $id_guru = Auth::user()->guru->id ?? 0;
        $riwayat = Presensi::where('user_id', $id_guru)
            ->where('role', 'guru')
            ->whereIn('status_kehadiran', ['Izin', 'Sakit', 'Dinas Luar'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return Inertia::render('Guru/Presensi/Izin', [
            'riwayat' => $riwayat
        ]);
    }

    public function ajukan(Request $request)
    {
        $id_guru = Auth::user()->guru->id ?? 0;

        $namaFile = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/surat_izin'), $namaFile);
        }

        $tanggal = $request->input('tanggal');

        $cek = Presensi::where('user_id', $id_guru)
            ->where('tanggal', $tanggal)
            ->count();

        if ($cek > 0) {
            return redirect()->route('guru.presensi.izin')->with('error', 'Anda sudah absen hari ini.');
        }

        Presensi::create([
            'user_id' => $id_guru,
            'role' => 'guru',
            'tanggal' => $tanggal,
            'status_kehadiran' => $request->input('status'),
            'keterangan' => $request->input('keterangan'),
            'bukti_izin' => $namaFile,
            'metode' => 'Online',
            'status_verifikasi' => 'Pending'
        ]);

        return redirect()->route('guru.presensi.izin')->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function rekap(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_guru = Auth::user()->guru->id ?? 0;

        $selected_time = strtotime($bulan . '-01');
        $prev_month_time = strtotime('-1 month', $selected_time);
        
        $start_date = date('Y-m', $prev_month_time) . '-19';
        $end_date = $bulan . '-18';

        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $absen = Presensi::where('user_id', $id_guru)
            ->where('role', 'guru')
            ->whereBetween('tanggal', [$start_date, $end_date])
            ->get();

        $absen_db_map = [];
        foreach ($absen as $a) {
            $verif = $a->status_verifikasi ?? 'Disetujui';
            $absen_db_map[$a->tanggal] = [
                'status' => $a->status_kehadiran, 
                'verif' => $verif,
                'menit' => $a->menit_terlambat ?? 0
            ];
        }

        $libur_array = [];
        $liburs = \App\Models\HariLibur::whereBetween('tanggal', [$start_date, $end_date])->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = $lbr->tanggal;
        }

        $map = [];
        $total = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0];
        $total_menit = 0;
        $hari_ini_real = date('Y-m-d');

        foreach ($dates as $tgl) {
            $kode_hari = date('N', strtotime($tgl));
            $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
            $is_libur_nasional = in_array($tgl, $libur_array);
            $is_future = (strtotime($tgl) > strtotime($hari_ini_real));

            $data_tgl = $absen_db_map[$tgl] ?? null;

            if ($is_weekend || $is_libur_nasional || $is_future) {
                $status_final = '-';
            } else {
                if ($data_tgl) {
                    $st_asli = $data_tgl['status'];
                    $verif = $data_tgl['verif'];
                    $status_final = (in_array($st_asli, ['Izin', 'Sakit', 'Dinas Luar']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
                    
                    if ($status_final == 'Terlambat') {
                        $total_menit += $data_tgl['menit'];
                    }
                } else {
                    $status_final = 'Alpha';
                }
            }

            $map[$tgl] = $status_final;

            if ($status_final == 'Hadir') $total['H']++;
            if ($status_final == 'Terlambat') { $total['T']++; $total['H']++; }
            if ($status_final == 'Sakit') $total['S']++;
            if ($status_final == 'Izin') $total['I']++;
            if ($status_final == 'Alpha') $total['A']++;
        }
        
        $jam_t = floor($total_menit / 60);
        $menit_t = $total_menit % 60;
        $format_terlambat = ($jam_t > 0 ? $jam_t . 'j ' : '') . ($menit_t > 0 || $jam_t == 0 ? $menit_t . 'm' : '');

        return Inertia::render('Guru/Presensi/Rekap', [
            'bulan' => $bulan,
            'map' => $map,
            'total' => $total,
            'dates' => $dates,
            'format_terlambat' => $format_terlambat
        ]);
    }

    public function cetakRekap(Request $request)
    {
        $bulan = $request->get('bulan') ?? date('Y-m');
        $id_guru = Auth::user()->guru->id ?? 0;
        
        $selected_time = strtotime($bulan . '-01');
        $prev_month_time = strtotime('-1 month', $selected_time);
        
        $start_date = date('Y-m', $prev_month_time) . '-19';
        $end_date = $bulan . '-18';

        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $guru = Guru::find($id_guru);
        $nama_guru = $guru->nama_guru ?? $guru->nama_lengkap ?? $guru->nama;

        $absen = Presensi::where('user_id', $id_guru)
            ->where('role', 'guru')
            ->whereBetween('tanggal', [$start_date, $end_date])
            ->get();

        $absen_db_map = [];
        foreach ($absen as $a) {
            $verif = $a->status_verifikasi ?? 'Disetujui';
            $absen_db_map[$a->tanggal] = [
                'status' => $a->status_kehadiran, 
                'verif' => $verif,
                'menit' => $a->menit_terlambat ?? 0
            ];
        }

        $libur_array = [];
        $liburs = \App\Models\HariLibur::whereBetween('tanggal', [$start_date, $end_date])->get();
        foreach ($liburs as $lbr) {
            $libur_array[] = $lbr->tanggal;
        }

        $map = [];
        $total = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'T' => 0];
        $total_menit = 0;
        $hari_ini_real = date('Y-m-d');

        foreach ($dates as $tgl) {
            $kode_hari = date('N', strtotime($tgl));
            $is_weekend = ($kode_hari == 6 || $kode_hari == 7);
            $is_libur_nasional = in_array($tgl, $libur_array);
            $is_future = (strtotime($tgl) > strtotime($hari_ini_real));

            $data_tgl = $absen_db_map[$tgl] ?? null;

            if ($is_weekend || $is_libur_nasional || $is_future) {
                $status_final = '-';
            } else {
                if ($data_tgl) {
                    $st_asli = $data_tgl['status'];
                    $verif = $data_tgl['verif'];
                    $status_final = (in_array($st_asli, ['Izin', 'Sakit', 'Dinas Luar']) && $verif !== 'Disetujui') ? 'Alpha' : $st_asli;
                    
                    if ($status_final == 'Terlambat') {
                        $total_menit += $data_tgl['menit'];
                    }
                } else {
                    $status_final = 'Alpha';
                }
            }

            $map[$tgl] = $status_final;

            if ($status_final == 'Hadir') $total['H']++;
            if ($status_final == 'Terlambat') { $total['T']++; $total['H']++; }
            if ($status_final == 'Sakit') $total['S']++;
            if ($status_final == 'Izin') $total['I']++;
            if ($status_final == 'Alpha') $total['A']++;
        }
        
        $jam_t = floor($total_menit / 60);
        $menit_t = $total_menit % 60;
        $format_terlambat = ($jam_t > 0 ? $jam_t . 'j ' : '') . ($menit_t > 0 || $jam_t == 0 ? $menit_t . 'm' : '');

        return view('presensi.cetak_rekap_guru', [
            'guru' => $guru,
            'nama_guru' => $nama_guru,
            'bulan' => $bulan,
            'map' => $map,
            'total' => $total,
            'dates' => $dates,
            'format_terlambat' => $format_terlambat,
            'sekolah' => Sekolah::find(1)
        ]);
    }
}
