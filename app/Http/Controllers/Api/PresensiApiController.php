<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PresensiApiController extends Controller
{
    // ==========================================
    // 1. CARI ID SISWA
    // ==========================================
    private function getRealSiswaID($nisn)
    {
        $siswa = DB::table('tbl_siswa')->where('nisn', $nisn)->first();
        if (!$siswa && Schema::hasColumn('tbl_siswa', 'nis')) {
            $siswa = DB::table('tbl_siswa')->where('nis', $nisn)->first();
        }
        if ($siswa) return $siswa->id;
        return null;
    }

    // ==========================================
    // 2. HITUNG JARAK LOKASI GPS
    // ==========================================
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

    // ==========================================
    // 3. SUBMIT ABSEN VIA SCAN QR ANDROID
    // ==========================================
    public function submitAbsen(Request $request)
    {
        $nisn      = $request->input('nisn');
        $lat_user  = $request->input('latitude');
        $long_user = $request->input('longitude');
        $qr_token  = $request->input('qr_token'); // Menangkap hasil scan QR dari Android

        if (empty($nisn) || empty($lat_user) || empty($long_user) || empty($qr_token)) {
            return response()->json(['status' => false, 'message' => 'Data tidak lengkap. Pastikan GPS & QR terbaca.'], 400);
        }

        $id_siswa = $this->getRealSiswaID($nisn);
        if (!$id_siswa) return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);

        $setting = DB::table('tbl_jam_sekolah')->where('id', 1)->first();
        if (!$setting) return response()->json(['status' => false, 'message' => 'Setting Sekolah belum diatur.'], 500);

        // VALIDASI QR TOKEN
        if ($qr_token !== ($setting->qr_token ?? '')) {
            return response()->json(['status' => false, 'message' => 'QR Code tidak valid atau sudah kadaluarsa!'], 400);
        }

        // VALIDASI RADIUS
        $jarak_meter = $this->hitungJarak($setting->latitude, $setting->longitude, $lat_user, $long_user);
        if ($jarak_meter > $setting->radius) {
            return response()->json(['status' => false, 'message' => "Di luar radius! Jarak Anda: {$jarak_meter}m (Maks: {$setting->radius}m)."], 400);
        }

        $hari_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        $cek = DB::table('tbl_presensi')->where(['user_id' => $id_siswa, 'role' => 'siswa', 'tanggal' => $hari_ini])->first();

        if (!$cek) {
            // PROSES ABSEN MASUK
            if ($jam_sekarang < $setting->jam_masuk_mulai) return response()->json(['status' => false, 'message' => 'Belum waktunya absen masuk.'], 400);
            
            // HITUNG KETERLAMBATAN & STATUS
            $batas_scan = $setting->jam_masuk_akhir;
            $mulai_terlambat = $setting->jam_masuk_mulai_terlambat ?? '07:00:00';

            if ($jam_sekarang > $batas_scan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Batas waktu absen masuk telah lewat (' . $batas_scan . ').'
                ], 400);
            }

            $status = 'Hadir';
            $menit_terlambat = 0;

            if ($jam_sekarang > $mulai_terlambat) {
                $status = 'Terlambat';
                $mulai_time = strtotime($mulai_terlambat);
                $sekarang_time = strtotime($jam_sekarang);
                $menit_terlambat = floor(($sekarang_time - $mulai_time) / 60);
            }

            DB::table('tbl_presensi')->insert([
                'user_id' => $id_siswa, 
                'role' => 'siswa', 
                'tanggal' => $hari_ini, 
                'jam_masuk' => $jam_sekarang,
                'latitude' => $lat_user, 
                'longitude' => $long_user, 
                'jarak_meter' => $jarak_meter,
                'status_kehadiran' => $status,
                'menit_terlambat' => $menit_terlambat,
                'bukti_izin' => 'QR_CODE_SCAN', 
                'metode' => 'Online', 
                'status_verifikasi' => 'Disetujui'
            ]);
            return response()->json(['status' => true, 'message' => 'Absen Masuk Berhasil!'], 200);
        } else {
            // PROSES ABSEN PULANG
            if (!empty($cek->jam_pulang) && $cek->jam_pulang != '00:00:00') return response()->json(['status' => false, 'message' => 'Anda sudah absen pulang hari ini.'], 400);
            if ($jam_sekarang < $setting->jam_pulang_mulai) return response()->json(['status' => false, 'message' => 'Sabar bos, belum waktunya pulang.'], 400);

            DB::table('tbl_presensi')->where('id', $cek->id)->update(['jam_pulang' => $jam_sekarang]);
            return response()->json(['status' => true, 'message' => 'Absen Pulang Berhasil!'], 200);
        }
    }

    // ==========================================
    // 4. AMBIL SETTING SEKOLAH (Untuk Cek Jarak di Android)
    // ==========================================
    public function getSetting()
    {
        $setting = DB::table('tbl_jam_sekolah')->where('id', 1)->first();
        if ($setting) {
            return response()->json(['status' => true, 'data' => $setting], 200);
        }
        return response()->json(['status' => false, 'message' => 'Setting sekolah belum diatur admin'], 404);
    }

    // ==========================================
    // 5. AMBIL RIWAYAT ABSENSI
    // ==========================================
    public function getRiwayat(Request $request)
    {
        $nisn = $request->input('nisn');
        if (!$nisn) return response()->json(['status' => false, 'message' => 'NISN wajib dikirim'], 400);

        $id_siswa = $this->getRealSiswaID($nisn);
        if (!$id_siswa) return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan'], 404);

        $riwayat = DB::table('tbl_presensi')
            ->where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->orderBy('tanggal', 'DESC')
            ->limit(7)
            ->get();

        return response()->json(['status' => true, 'data' => $riwayat], 200);
    }

    // ==========================================
    // 6. API REKAP BULANAN
    // ==========================================
    public function getRekap(Request $request)
    {
        $nisn = $request->input('nisn');
        $bulan = $request->input('bulan') ?? date('Y-m');
        $id_siswa = $this->getRealSiswaID($nisn);

        if (!$id_siswa) return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);

        $absen = DB::table('tbl_presensi')
            ->where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->where('tanggal', 'like', "%$bulan%")
            ->get();

        $total = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
        foreach ($absen as $a) {
            $st = $a->status_kehadiran;
            if ($st == 'Hadir') $total['hadir']++;
            if ($st == 'Terlambat') { $total['terlambat']++; $total['hadir']++; }
            if ($st == 'Sakit') $total['sakit']++;
            if ($st == 'Izin') $total['izin']++;
        }

        return response()->json([
            'status' => true,
            'bulan' => $bulan,
            'summary' => $total,
            'data' => $absen
        ], 200);
    }

    // ==========================================
    // 7. API AJUKAN IZIN / SAKIT
    // ==========================================
    public function ajukanIzin(Request $request)
    {
        try {
            $nisn = $request->input('nisn');
            $tgl  = $request->input('tanggal');
            $st   = $request->input('status');
            $ket  = $request->input('keterangan');
            $foto_base64 = $request->input('file_bukti');

            $id_siswa = $this->getRealSiswaID($nisn);
            if (!$id_siswa) return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);

            $cek = DB::table('tbl_presensi')->where(['user_id' => $id_siswa, 'tanggal' => $tgl])->count();
            if ($cek > 0) return response()->json(['status' => false, 'message' => 'Sudah ada data presensi pada tanggal tersebut.'], 400);

            $nama_file = '';
            if (!empty($foto_base64)) {
                $nama_file = 'izin_' . date('Ymd_His') . '_' . $id_siswa . '.jpg';
                $path = public_path('uploads/surat_izin/');
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                }
                @file_put_contents($path . $nama_file, base64_decode($foto_base64));
            }

            // Normalisasi status ke huruf kapital depan (karena Strict Mode DB)
            if ($st) {
                $st = ucfirst(strtolower(trim($st))); // izin -> Izin, sakit -> Sakit
            }

            DB::table('tbl_presensi')->insert([
                'user_id'           => $id_siswa,
                'role'              => 'siswa',
                'tanggal'           => $tgl,
                'status_kehadiran'  => $st,
                'keterangan'        => $ket,
                'bukti_izin'        => $nama_file,
                'metode'            => 'Online',
                'status_verifikasi' => 'Pending',
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ]);

            return response()->json(['status' => true, 'message' => 'Pengajuan berhasil dikirim! Menunggu verifikasi admin.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 8. API SUMMARY (Statistik Kehadiran)
    // ==========================================
    public function getSummary(Request $request)
    {
        $nisn = $request->input('nisn');
        $id_siswa = $this->getRealSiswaID($nisn);

        if (!$id_siswa) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $absen = DB::table('tbl_presensi')
            ->where('user_id', $id_siswa)
            ->where('role', 'siswa')
            ->get();

        $hadir = 0; $sakit = 0; $izin = 0; $alfa = 0; $terlambat = 0;
        foreach ($absen as $a) {
            $st = strtolower(trim($a->status_kehadiran));
            if ($st == 'hadir') $hadir++;
            elseif ($st == 'terlambat') { $terlambat++; $hadir++; }
            elseif ($st == 'sakit') $sakit++;
            elseif ($st == 'izin') $izin++;
            elseif ($st == 'alfa' || $st == 'alpha') $alfa++;
        }

        $total_days = $hadir + $sakit + $izin + $alfa;
        $total_percentage = $total_days > 0 ? round(($hadir / $total_days) * 100) : 100;

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_percentage' => $total_percentage,
                'hadir' => $hadir,
                'alfa' => $alfa,
                'sakit' => $sakit,
                'terlambat' => $terlambat
            ]
        ], 200);
    }

    // ==========================================
    // 9. API LOCATION SETTING
    // ==========================================
    public function getLocationSetting()
    {
        $setting = DB::table('tbl_jam_sekolah')->where('id', 1)->first();
        if ($setting) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'school_latitude' => (float) $setting->latitude,
                    'school_longitude' => (float) $setting->longitude,
                    'radius_meters' => (int) $setting->radius
                ]
            ], 200);
        }
        return response()->json(['status' => 'error', 'message' => 'Setting belum diatur'], 404);
    }

    // ==========================================
    // 10. API TODAY STATUS
    // ==========================================
    public function getTodayStatus(Request $request)
    {
        $nisn = $request->input('nisn');
        $id_siswa = $this->getRealSiswaID($nisn);

        if (!$id_siswa) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $hari_ini = date('Y-m-d');
        $cek = DB::table('tbl_presensi')
            ->where(['user_id' => $id_siswa, 'role' => 'siswa', 'tanggal' => $hari_ini])
            ->first();

        if (!$cek) {
            $status = 'Belum Absen';
            $button_text = 'Absen Masuk';
        } else {
            if (empty($cek->jam_pulang) || $cek->jam_pulang == '00:00:00') {
                $status = 'Sudah Masuk';
                $button_text = 'Absen Pulang';
            } else {
                $status = 'Selesai Absen';
                $button_text = 'Selesai';
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'status_hari_ini' => $status,
                'button_text' => $button_text,
                'jam_masuk' => $cek->jam_masuk ?? null,
                'jam_pulang' => $cek->jam_pulang ?? null
            ]
        ], 200);
    }
}
