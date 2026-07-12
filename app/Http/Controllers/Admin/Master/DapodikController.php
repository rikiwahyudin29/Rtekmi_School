<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DapodikSetting;
use App\Services\DapodikService;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class DapodikController extends Controller
{
    public function index()
    {
        $setting = DapodikSetting::first();
        if (!$setting) {
            $setting = DapodikSetting::create([
                'ip_dapodik' => 'http://localhost:5774',
                'key_integrasi' => '',
                'npsn' => '',
                'status_koneksi' => 'Gagal'
            ]);
        }
        return Inertia::render('Admin/Master/Dapodik/Index', [
            'setting' => $setting
        ]);
    }

    public function update(Request $request)
    {
        $setting = DapodikSetting::first();
        $setting->update([
            'ip_dapodik' => rtrim($request->ip_dapodik ?? 'http://localhost:5774', '/'),
            'key_integrasi' => $request->key_integrasi ?? '',
            'npsn' => $request->npsn ?? ''
        ]);
        return redirect()->back()->with('success', 'Pengaturan Dapodik berhasil disimpan.');
    }

    public function testConnection(DapodikService $dapodikService)
    {
        $setting = DapodikSetting::first();
        try {
            $isConnected = $dapodikService->testConnection();

            if ($isConnected) {
                $setting->update(['status_koneksi' => 'Terhubung']);
                return redirect()->back()->with('success', "Koneksi SUKSES! Terhubung ke Web Service Dapodik.");
            } else {
                $setting->update(['status_koneksi' => 'Gagal']);
                return redirect()->back()->with('error', 'Gagal terhubung. Pastikan IP dan Token benar, serta aplikasi Dapodik aktif.');
            }
        } catch (\Exception $e) {
            $setting->update(['status_koneksi' => 'Gagal']);
            return redirect()->back()->with('error', 'Koneksi Gagal: ' . $e->getMessage());
        }
    }

    public function sync($type, DapodikService $dapodikService)
    {
        try {
            $count = 0;
            if ($type == 'sekolah') {
                $data = $dapodikService->get('getSekolah');
                $count = count($data);
                // Lakukan pembaruan profil sekolah (tbl_sekolah)
            } elseif ($type == 'guru') {
                $data = $dapodikService->get('getGtk');
                $count = count($data);
                // Lakukan insert/update tbl_guru berdasarkan nuptk/nik
                foreach ($data as $gtk) {
                    $nik = $gtk['nik'] ?? null;
                    $userId = null;
                    if ($nik) {
                        $user = \App\Models\User::firstOrNew(['username' => $nik]);
                        $user->nama_lengkap = $gtk['nama'] ?? '-';
                        $user->role = 'guru';
                        if (!$user->exists) {
                            $user->password = \Illuminate\Support\Facades\Hash::make($nik);
                        }
                        $user->save();
                        $userId = $user->id;

                        // Pastikan role 'guru' ditambahkan di tabel user_roles
                        $roleId = \App\Models\Role::where('role_key', 'guru')->value('id');
                        if ($roleId) {
                            \Illuminate\Support\Facades\DB::table('user_roles')->updateOrInsert(
                                ['user_id' => $userId, 'role_id' => $roleId]
                            );
                        }
                    }

                    \App\Models\Guru::updateOrCreate(
                        ['dapodik_id' => $gtk['ptk_id'] ?? null], // Kunci utama pencarian (ID Dapodik)
                        [
                            'user_id' => $userId,
                            'nama_lengkap' => $gtk['nama'] ?? '-',
                            'nik' => $nik,
                            'nuptk' => $gtk['nuptk'] ?? null,
                            'jk' => isset($gtk['jenis_kelamin']) && $gtk['jenis_kelamin'] == 'P' ? 'P' : 'L',
                            'jenis_kelamin' => isset($gtk['jenis_kelamin']) && $gtk['jenis_kelamin'] == 'P' ? 'P' : 'L',
                            'tempat_lahir' => $gtk['tempat_lahir'] ?? null,
                            'tanggal_lahir' => $gtk['tanggal_lahir'] ?? null,
                            'tgl_lahir' => $gtk['tanggal_lahir'] ?? null,
                            'alamat' => $gtk['alamat_jalan'] ?? null,
                            'no_hp' => $gtk['no_hp'] ?? null,
                            'email' => $gtk['email'] ?? null,
                            'status_kepegawaian' => $gtk['status_kepegawaian_id_str'] ?? null,
                            // Set default jika belum ada
                            'status_guru' => 'GTY', 
                        ]
                    );
                }
            } elseif ($type == 'siswa') {
                $data = $dapodikService->get('getPesertaDidik');
                $count = count($data);
                // Lakukan insert/update tbl_siswa berdasarkan nisn/nik
                foreach ($data as $pd) {
                    $nisn = $pd['nisn'] ?? null;
                    $userId = null;
                    if ($nisn) {
                        $user = \App\Models\User::firstOrNew(['username' => $nisn]);
                        $user->nama_lengkap = $pd['nama'] ?? '-';
                        $user->role = 'siswa';
                        if (!$user->exists) {
                            $user->password = \Illuminate\Support\Facades\Hash::make($nisn);
                        }
                        $user->save();
                        $userId = $user->id;

                        // Pastikan role 'siswa' ditambahkan di tabel user_roles
                        $roleId = \App\Models\Role::where('role_key', 'siswa')->value('id');
                        if ($roleId) {
                            \Illuminate\Support\Facades\DB::table('user_roles')->updateOrInsert(
                                ['user_id' => $userId, 'role_id' => $roleId]
                            );
                        }
                    }

                    // Cari ID kelas lokal (tbl_kelas.id) berdasarkan rombongan_belajar_id dari Dapodik
                    $kelasId = null;
                    if (!empty($pd['rombongan_belajar_id'])) {
                        $kelasId = \App\Models\Kelas::where('dapodik_id', $pd['rombongan_belajar_id'])->value('id');
                    }

                    \App\Models\Siswa::updateOrCreate(
                        ['dapodik_id' => $pd['peserta_didik_id'] ?? null],
                        [
                            'user_id' => $userId,
                            'kelas_id' => $kelasId,
                            'nama_lengkap' => $pd['nama'] ?? '-',
                            'nisn' => $nisn ?? '-',
                            'nik' => $pd['nik'] ?? null,
                            'jk' => isset($pd['jenis_kelamin']) && $pd['jenis_kelamin'] == 'P' ? 'P' : 'L',
                            'jenis_kelamin' => isset($pd['jenis_kelamin']) && $pd['jenis_kelamin'] == 'P' ? 'P' : 'L',
                            'tempat_lahir' => $pd['tempat_lahir'] ?? null,
                            'tanggal_lahir' => $pd['tanggal_lahir'] ?? null,
                            'tgl_lahir' => $pd['tanggal_lahir'] ?? null,
                            'agama' => $pd['agama_id_str'] ?? null,
                            'alamat' => $pd['alamat_jalan'] ?? null,
                            'nama_ayah' => $pd['nama_ayah'] ?? null,
                            'nama_ibu' => $pd['nama_ibu_kandung'] ?? null,
                            'nama_wali' => $pd['nama_wali'] ?? null,
                            'no_hp_siswa' => $pd['nomor_telepon_seluler'] ?? null,
                            'email_siswa' => $pd['email'] ?? null,
                            'rombel_id_dapodik' => $pd['rombongan_belajar_id'] ?? null,
                            'status_siswa' => 'Aktif',
                        ]
                    );
                }
            } elseif ($type == 'rombel') {
                $data = $dapodikService->get('getRombonganBelajar');
                $count = count($data);
                
                // Sinkronisasi Jurusan dan Kelas (Rombel)
                foreach ($data as $rombel) {
                    $jurusanId = null;
                    if (!empty($rombel['jurusan_sp_id']) || !empty($rombel['jurusan_id_str'])) {
                        $namaJurusan = $rombel['jurusan_id_str'] ?? 'Umum';
                        $jurusan = \App\Models\Jurusan::updateOrCreate(
                            ['dapodik_id' => $rombel['jurusan_sp_id'] ?? $namaJurusan],
                            [
                                'kode_jurusan' => substr($namaJurusan, 0, 10),
                                'nama_jurusan' => $namaJurusan,
                            ]
                        );
                        $jurusanId = $jurusan->id;
                    }

                    \App\Models\Kelas::updateOrCreate(
                        ['dapodik_id' => $rombel['rombongan_belajar_id'] ?? null],
                        [
                            'nama_kelas' => $rombel['nama'] ?? '-',
                            'tingkat' => $rombel['tingkat_pendidikan_id'] ?? null,
                            'id_jurusan' => $jurusanId,
                        ]
                    );
                }
                
                // Karena tombol di UI menjadi satu (Tarik Rombel & Mapel), kita tarik juga mapel di sini
                try {
                    $dataMapel = $dapodikService->get('getMataPelajaran');
                    $count += count($dataMapel);
                    foreach ($dataMapel as $mapel) {
                        \App\Models\Mapel::updateOrCreate(
                            ['dapodik_id' => $mapel['mata_pelajaran_id'] ?? null],
                            [
                                'nama_mapel' => $mapel['nama'] ?? '-',
                                'kode_mapel' => $mapel['kode_mata_pelajaran'] ?? null,
                                'kelompok' => $mapel['kelompok'] ?? 'A',
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    // Abaikan jika mapel gagal agar rombel tetap sukses
                }

            } elseif ($type == 'mapel') {
                $data = $dapodikService->get('getMataPelajaran');
                $count = count($data);
                foreach ($data as $mapel) {
                    \App\Models\Mapel::updateOrCreate(
                        ['dapodik_id' => $mapel['mata_pelajaran_id'] ?? null],
                        [
                            'nama_mapel' => $mapel['nama'] ?? '-',
                            'kode_mapel' => $mapel['kode_mata_pelajaran'] ?? null,
                            'kelompok' => $mapel['kelompok'] ?? 'A',
                        ]
                    );
                }
            }

            return redirect()->back()->with('success', "Proses Tarik Data $type selesai! Berhasil mengambil $count baris data dari Dapodik.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal melakukan sinkronisasi $type: " . $e->getMessage());
        }
    }
}
