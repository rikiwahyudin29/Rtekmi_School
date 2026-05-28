<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresensiApiController extends Controller
{
    // ==========================================
    // 1. CARI ID SISWA
    // ==========================================
    private function getRealSiswaID($nisn)
    {
        $siswa = DB::table('tbl_siswa')->where('nisn', $nisn)->first();
        if (!$siswa) {
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
            if ($jam_sekarang > $setting->jam_masuk_akhir) return response()->json(['status' => false, 'message' => 'Batas absen masuk sudah habis.'], 400);

            DB::table('tbl_presensi')->insert([
                'user_id' => $id_siswa, 'role' => 'siswa', 'tanggal' => $hari_ini, 'jam_masuk' => $jam_sekarang,
                'latitude' => $lat_user, 'longitude' => $long_user, 'jarak_meter' => $jarak_meter,
                'bukti_izin' => 'QR_CODE_SCAN', 'status_kehadiran' => 'Hadir', 'metode' => 'Online', 'status_verifikasi' => 'Disetujui'
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
            ->where('tanggal', 'like', "$bulan%")
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
            if (!is_dir($path)) mkdir($path, 0777, true);
            file_put_contents($path . $nama_file, base64_decode($foto_base64));
        }

        DB::table('tbl_presensi')->insert([
            'user_id'           => $id_siswa,
            'role'              => 'siswa',
            'tanggal'           => $tgl,
            'status_kehadiran'  => $st,
            'keterangan'        => $ket,
            'bukti_izin'        => $nama_file,
            'metode'            => 'Online',
            'status_verifikasi' => 'Pending'
        ]);

        return response()->json(['status' => true, 'message' => 'Pengajuan berhasil dikirim! Menunggu verifikasi admin.'], 200);
    }
}
