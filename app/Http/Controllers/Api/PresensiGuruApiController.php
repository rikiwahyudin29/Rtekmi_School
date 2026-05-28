<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresensiGuruApiController extends Controller
{
    private function getRealGuruID($id_user_login)
    {
        // First check if it maps to tbl_guru.id_user (or similar mapping)
        // Adjust the column name to match the new Laravel schema
        // In the old system it was checking 'id_user' or 'user_id' in 'tbl_guru'
        $guru = DB::table('tbl_guru')->where('id_user', $id_user_login)->first();
        if ($guru) return $guru->id;
        
        $guru_direct = DB::table('tbl_guru')->where('id', $id_user_login)->first();
        if ($guru_direct) return $guru_direct->id;
        
        return $id_user_login;
    }

    // 1. CEK STATUS ABSEN HARI INI
    public function statusAbsenHariIni(Request $request)
    {
        $id_user = $request->input('id_user');
        $id_guru = $this->getRealGuruID($id_user);
        
        $setting = DB::table('tbl_jam_sekolah')->where('id', 1)->first();
        $hari_ini = date('Y-m-d');
        
        $absen = DB::table('tbl_presensi')
            ->where(['user_id' => $id_guru, 'role' => 'guru', 'tanggal' => $hari_ini])
            ->first();

        return response()->json([
            'status' => true,
            'lokasi_sekolah' => $setting,
            'data_absen' => $absen
        ], 200);
    }

    // 2. SUBMIT ABSEN GURU (SCAN QR)
    public function submitAbsen(Request $request)
    {
        $id_user   = $request->input('id_user');
        $lat_user  = $request->input('latitude');
        $long_user = $request->input('longitude');
        $qr_token  = $request->input('qr_token');
        
        $id_guru = $this->getRealGuruID($id_user);
        $setting = DB::table('tbl_jam_sekolah')->where('id', 1)->first();
        
        $today = date('Y-m-d');
        $now   = date('H:i:s');

        if ($qr_token !== ($setting->qr_token ?? '')) {
            return response()->json(['status' => false, 'message' => 'QR Code tidak valid.'], 400);
        }

        $cek = DB::table('tbl_presensi')->where(['user_id' => $id_guru, 'role' => 'guru', 'tanggal' => $today])->first();

        if (!$cek) {
            DB::table('tbl_presensi')->insert([
                'user_id' => $id_guru, 'role' => 'guru', 'tanggal' => $today, 'jam_masuk' => $now,
                'latitude' => $lat_user, 'longitude' => $long_user,
                'bukti_izin' => 'QR_CODE_SCAN', 'status_kehadiran' => 'Hadir', 'metode' => 'QRCode/Geo', 'status_verifikasi' => 'Terverifikasi'
            ]);
            return response()->json(['status' => true, 'message' => "Berhasil Absen Masuk Jam $now"], 200);
        } else {
            if (!empty($cek->jam_pulang) && $cek->jam_pulang != '00:00:00') {
                return response()->json(['status' => false, 'message' => 'Anda sudah absen pulang hari ini.'], 400);
            }
            DB::table('tbl_presensi')->where('id', $cek->id)->update(['jam_pulang' => $now]);
            return response()->json(['status' => true, 'message' => "Berhasil Absen Pulang Jam $now"], 200);
        }
    }

  // 3. API REKAP BULANAN GURU (ANTI-BADAI)
    public function getRekap(Request $request)
    {
        $id_user = $request->input('id_user');
        $id_guru = $this->getRealGuruID($id_user);
        
        $bulan = $request->input('bulan') ?? date('Y-m');

        $absen = DB::table('tbl_presensi')
            ->select('tanggal', 'jam_masuk', 'jam_pulang', 'status_kehadiran')
            ->where('user_id', $id_guru)
            ->where('role', 'guru')
            ->where('tanggal', 'like', "$bulan%")
            ->orderBy('tanggal', 'DESC')
            ->get()->toArray();

        // Hitung Summary Cepat
        $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
        
        foreach ($absen as &$a) {
            // LOGIKA SELF-HEALING (Perbaikan Data Otomatis)
            // Jika status_kehadiran kosong TAPI ada jam_masuk, paksa jadi "Hadir"
            if (empty($a->status_kehadiran) && !empty($a->jam_masuk)) {
                $a->status_kehadiran = 'Hadir';
            }

            $st = strtolower($a->status_kehadiran ?? '');
            if ($st == 'hadir') $summary['hadir']++;
            if ($st == 'sakit') $summary['sakit']++;
            if ($st == 'izin' || $st == 'dinas luar') $summary['izin']++;
            if ($st == 'alpha') $summary['alpha']++;
            if ($st == 'terlambat') { $summary['terlambat']++; $summary['hadir']++; }
        }

        return response()->json([
            'status'  => true,
            'bulan'   => $bulan,
            'summary' => $summary,
            'data'    => $absen // Data yang sudah diperbaiki otomatis dikirim ke Android
        ], 200);
    }
    
    // 4. API SUBMIT IZIN GURU (BARU)
    public function submitIzin(Request $request)
    {
        $id_user    = $request->input('id_user');
        $id_guru    = $this->getRealGuruID($id_user);
        $tanggal    = $request->input('tanggal');
        $status     = $request->input('status'); // Izin / Sakit
        $keterangan = $request->input('keterangan');

        $cek = DB::table('tbl_presensi')->where(['user_id' => $id_guru, 'tanggal' => $tanggal])->count();
        if ($cek > 0) return response()->json(['status' => false, 'message' => 'Anda sudah memiliki riwayat absen/izin di tanggal ini.'], 400);

        // Upload berkas dari Kamera HP (Format Base64 dari Android)
        $base64Image = $request->input('bukti');
        $namaFile = null;
        
        if (!empty($base64Image)) {
            $image_base64 = base64_decode($base64Image);
            $namaFile = uniqid() . '.jpg';
            $path = public_path('uploads/surat_izin/');
            if (!is_dir($path)) mkdir($path, 0777, true);
            file_put_contents($path . $namaFile, $image_base64);
        }

        DB::table('tbl_presensi')->insert([
            'user_id' => $id_guru,
            'role' => 'guru',
            'tanggal' => $tanggal,
            'status_kehadiran' => $status,
            'keterangan' => $keterangan,
            'bukti_izin' => $namaFile,
            'metode' => 'Online',
            'status_verifikasi' => 'Pending'
        ]);

        return response()->json(['status' => true, 'message' => 'Pengajuan izin berhasil dikirim.'], 200);
    }
}
