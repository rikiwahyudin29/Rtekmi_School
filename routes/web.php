<?php

use Illuminate\Support\Facades\Route;

// Public Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Master Admin Controllers
use App\Http\Controllers\Admin\Master\TahunAjaranController;
use App\Http\Controllers\Admin\Master\JurusanController;
use App\Http\Controllers\Admin\Master\KelasController;
use App\Http\Controllers\Admin\Master\MapelController;
use App\Http\Controllers\Admin\Master\RuanganController;
use App\Http\Controllers\Admin\Master\JenisUjianController;
use App\Http\Controllers\Admin\Master\JamBelajarController;
use App\Http\Controllers\Admin\Master\SekolahController;
use App\Http\Controllers\Admin\Master\BackupController;
use App\Http\Controllers\Admin\Master\DapodikController;

// Admin General Controllers
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\SiswaController;

// Kurikulum Controllers
use App\Http\Controllers\Admin\Kurikulum\JadwalPelajaranController;
use App\Http\Controllers\Admin\Kurikulum\PembagianTugasController;
use App\Http\Controllers\Admin\CBT\BankSoalController;

// Other Controllers
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;

// Test Route
Route::get('/test-jadwal-kelas/{id}', function ($id) {
    $count = \App\Models\UjianSiswa::where('jadwal_id', $id)->count();
    $peserta = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
        ->where('us.jadwal_id', $id)
        ->get();
        
    $joined = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
        ->leftJoin('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
        ->leftJoin('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
        ->where('us.jadwal_id', $id)
        ->select('us.siswa_id', 'ms.kelas_id', 'mk.nama_kelas')
        ->get();
        
    $kelas_concat = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
        ->join('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
        ->join('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
        ->where('us.jadwal_id', $id)
        ->selectRaw("GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas")
        ->value('kelas');
        
    return response()->json([
        'count' => $count,
        'peserta' => $peserta,
        'joined' => $joined,
        'kelas_concat' => $kelas_concat
    ]);
});

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/verifikasi/{token}', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'verifikasi'])->name('surat.verifikasi');
Route::get('/verifikasi/{token}/cetak', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'cetakPublic'])->name('surat.verifikasi.cetak');
Route::get('/debug-siswa', function() {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) return 'Not logged in';
    $siswa = \Illuminate\Support\Facades\DB::table('tbl_siswa')->where('user_id', $user->id)->first();
    $siswa2 = \Illuminate\Support\Facades\DB::table('tbl_siswa')->where('nisn', $user->username)->first();
    return [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => session('role_active'),
        'siswa_by_user_id' => $siswa,
        'siswa_by_username' => $siswa2,
    ];
});
Route::post('/cek-saldo', [HomeController::class, 'cekSaldo'])->name('cek-saldo');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Group
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Admin Master Group
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('tahun-ajaran', TahunAjaranController::class);
            Route::resource('jurusan', JurusanController::class);
            Route::resource('kelas', KelasController::class);
            Route::resource('mapel', MapelController::class);
            Route::resource('ruangan', RuanganController::class);
            Route::resource('jenis-ujian', JenisUjianController::class);
            
            // Sekolah specific routes (Single record, non-resource)
            Route::get('sekolah', [SekolahController::class, 'index'])->name('sekolah.index');
            Route::post('sekolah', [SekolahController::class, 'update'])->name('sekolah.update');
            
            // Jam Belajar specific routes
            Route::post('jam-belajar/sekolah/update', [JamBelajarController::class, 'updateSekolah'])->name('jam-belajar.sekolah.update');
            Route::post('jam-belajar/master/store', [JamBelajarController::class, 'storeMaster'])->name('jam-belajar.master.store');
            Route::put('jam-belajar/master/update/{id}', [JamBelajarController::class, 'updateMaster'])->name('jam-belajar.master.update');
            Route::delete('jam-belajar/master/destroy/{id}', [JamBelajarController::class, 'destroyMaster'])->name('jam-belajar.master.destroy');
            Route::resource('jam-belajar', JamBelajarController::class)->only(['index']);
            
            // Backup specific routes
            Route::post('backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
            Route::post('backup/hapusData', [BackupController::class, 'hapusData'])->name('backup.hapusData');
            Route::get('backup/database', [BackupController::class, 'database'])->name('backup.database');
            Route::resource('backup', BackupController::class)->only(['index']);

            // Dapodik specific routes
            Route::post('dapodik/update', [DapodikController::class, 'update'])->name('dapodik.update');
            Route::post('dapodik/test', [DapodikController::class, 'testConnection'])->name('dapodik.test');
            Route::post('dapodik/sync/{type}', [DapodikController::class, 'sync'])->name('dapodik.sync');
            Route::resource('dapodik', DapodikController::class)->only(['index']);
        });

        // Admin User/Data Group
        Route::post('guru/{id}/roles', [GuruController::class, 'syncRoles'])->name('guru.roles.sync');
        Route::resource('guru', GuruController::class);
        Route::resource('siswa', SiswaController::class);
        
        // Admin Kurikulum Group
        Route::prefix('kurikulum')->name('kurikulum.')->group(function () {
            // Pembagian Tugas
            Route::get('pembagian-tugas', [PembagianTugasController::class, 'index'])->name('pembagian-tugas.index');
            Route::post('pembagian-tugas/update', [PembagianTugasController::class, 'updateMapping'])->name('pembagian-tugas.update');

            // Jadwal Pelajaran
            Route::get('jadwal-pelajaran/template', [JadwalPelajaranController::class, 'templateExcel'])->name('jadwal-pelajaran.template');
            Route::post('jadwal-pelajaran/import', [JadwalPelajaranController::class, 'importExcel'])->name('jadwal-pelajaran.import');
            Route::get('jadwal-pelajaran/rekap', [JadwalPelajaranController::class, 'rekap'])->name('jadwal-pelajaran.rekap');
            Route::get('jadwal-pelajaran/cetak-rekap', [JadwalPelajaranController::class, 'cetakRekap'])->name('jadwal-pelajaran.cetak_rekap');
            Route::get('jadwal-pelajaran/cetak', [JadwalPelajaranController::class, 'cetak'])->name('jadwal-pelajaran.cetak');
            Route::resource('jadwal-pelajaran', JadwalPelajaranController::class)->except(['create', 'edit', 'show']);
        });

        // Admin Presensi Group
        Route::prefix('presensi')->name('presensi.')->group(function () {
            Route::get('scanner', [\App\Http\Controllers\Admin\PresensiController::class, 'scanner'])->name('scanner');
            Route::post('proses-scan', [\App\Http\Controllers\Admin\PresensiController::class, 'prosesScan'])->name('proses_scan');
            Route::get('/hari_libur', [\App\Http\Controllers\Admin\HariLiburController::class, 'index'])->name('hari_libur.index');
            Route::post('/hari_libur', [\App\Http\Controllers\Admin\HariLiburController::class, 'store'])->name('hari_libur.store');
            Route::delete('/hari_libur/{id}', [\App\Http\Controllers\Admin\HariLiburController::class, 'destroy'])->name('hari_libur.destroy');
            Route::get('/kartu', [\App\Http\Controllers\Admin\KartuController::class, 'index'])->name('kartu.index');
            Route::get('/kartu/cetak-siswa', [\App\Http\Controllers\Admin\KartuController::class, 'cetakSiswa'])->name('kartu.cetak_siswa');
            Route::get('/kartu/cetak-guru', [\App\Http\Controllers\Admin\KartuController::class, 'cetakGuru'])->name('kartu.cetak_guru');
            Route::post('/kartu/simpan-uid', [\App\Http\Controllers\Admin\KartuController::class, 'simpanUid'])->name('kartu.simpan_uid');
            Route::get('izin', [\App\Http\Controllers\Admin\PresensiController::class, 'izin'])->name('izin');
            Route::post('simpan-izin', [\App\Http\Controllers\Admin\PresensiController::class, 'simpanIzin'])->name('simpan_izin');
            Route::delete('hapus-izin/{id}', [\App\Http\Controllers\Admin\PresensiController::class, 'hapusIzin'])->name('hapus_izin');
            Route::get('verifikasi/{id}/{status}', [\App\Http\Controllers\Admin\PresensiController::class, 'verifikasi'])->name('verifikasi');
            Route::get('get-siswa/{id_kelas}', [\App\Http\Controllers\Admin\PresensiController::class, 'getSiswaByKelas'])->name('get_siswa_by_kelas');
            
            Route::get('laporan', [\App\Http\Controllers\Admin\PresensiController::class, 'laporan'])->name('laporan');
            Route::get('cetak-harian', [\App\Http\Controllers\Admin\PresensiController::class, 'cetakHarian'])->name('cetak_harian');
            Route::get('cetak-harian-guru', [\App\Http\Controllers\Admin\PresensiController::class, 'cetakHarianGuru'])->name('cetak_harian_guru');
            
            Route::get('rekap', [\App\Http\Controllers\Admin\PresensiController::class, 'rekap'])->name('rekap');
            Route::get('cetak-rekap', [\App\Http\Controllers\Admin\PresensiController::class, 'cetakRekap'])->name('cetak_rekap');
            Route::get('cetak-matrix', [\App\Http\Controllers\Admin\PresensiController::class, 'cetakMatrix'])->name('cetak_matrix');
            Route::get('cetak-matrix-guru', [\App\Http\Controllers\Admin\PresensiController::class, 'cetakMatrixGuru'])->name('cetak_matrix_guru');
            
            Route::get('setting-jam', [\App\Http\Controllers\Admin\JamPresensiController::class, 'index'])->name('setting_jam');
            Route::post('setting-jam', [\App\Http\Controllers\Admin\JamPresensiController::class, 'update'])->name('setting_jam.update');
        });

        // Admin Surat & E-Office Group
        Route::prefix('surat')->name('surat.')->group(function () {
            // Template Surat
            Route::post('template/bulk-destroy', [\App\Http\Controllers\Admin\Surat\TemplateSuratController::class, 'bulkDestroy'])->name('template.bulk-destroy');
            Route::resource('template', \App\Http\Controllers\Admin\Surat\TemplateSuratController::class)->except(['create', 'show', 'edit']);
            
            // Surat Keluar
            Route::post('keluar/bulk-destroy', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'bulkDestroy'])->name('keluar.bulk-destroy');
            Route::get('keluar/{id}/cetak', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'cetak'])->name('keluar.cetak');
            Route::resource('keluar', \App\Http\Controllers\Admin\Surat\SuratKeluarController::class)->except(['create', 'show', 'edit', 'update']);
            
            // Surat Masuk
            Route::post('masuk/bulk-destroy', [\App\Http\Controllers\Admin\Surat\SuratMasukController::class, 'bulkDestroy'])->name('masuk.bulk-destroy');
            Route::post('masuk/disposisi', [\App\Http\Controllers\Admin\Surat\SuratMasukController::class, 'disposisi'])->name('masuk.disposisi');
            Route::resource('masuk', \App\Http\Controllers\Admin\Surat\SuratMasukController::class)->except(['create', 'show', 'edit', 'update']);
            
            // E-Arsip
            Route::get('arsip', [\App\Http\Controllers\Admin\Surat\EArsipController::class, 'index'])->name('arsip.index');
        });
        // Admin Kelulusan & SKL Group
        Route::prefix('kelulusan')->name('kelulusan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\KelulusanController::class, 'index'])->name('index');
            Route::post('/simpan-massal', [\App\Http\Controllers\Admin\KelulusanController::class, 'simpanMassal'])->name('simpan_massal');
            Route::get('/setting', [\App\Http\Controllers\Admin\KelulusanController::class, 'setting'])->name('setting');
            Route::post('/setting', [\App\Http\Controllers\Admin\KelulusanController::class, 'simpanSetting'])->name('setting.simpan');
            Route::get('/nilai', [\App\Http\Controllers\Admin\KelulusanController::class, 'nilai'])->name('nilai');
            Route::get('/nilai/{id}/detail', [\App\Http\Controllers\Admin\KelulusanController::class, 'detailNilai'])->name('detail_nilai');
            Route::get('/nilai/{id}/input', [\App\Http\Controllers\Admin\KelulusanController::class, 'inputNilai'])->name('input_nilai');
            Route::post('/nilai/simpan', [\App\Http\Controllers\Admin\KelulusanController::class, 'simpanNilai'])->name('simpan_nilai');
            Route::get('/download-template', [\App\Http\Controllers\Admin\KelulusanController::class, 'downloadTemplate'])->name('download_template');
            Route::post('/import', [\App\Http\Controllers\Admin\KelulusanController::class, 'importNilai'])->name('import');
            Route::get('/cetak-transkrip/{id}', [\App\Http\Controllers\Admin\KelulusanController::class, 'cetakTranskrip'])->name('cetak_transkrip');
            Route::get('/cetak-skl/{id}', [\App\Http\Controllers\Admin\KelulusanController::class, 'cetakSkl'])->name('cetak_skl');
        });

        // Roles is inside the admin prefix, making the route name 'admin.roles.index'
        Route::resource('roles', RoleController::class);
        
        // Admins user management
        Route::resource('admins', AdminController::class);

        // CBT (Computer Based Test) Group
        Route::prefix('cbt')->name('cbt.')->group(function () {
            // Overview
            Route::get('/overview', [\App\Http\Controllers\Admin\CBT\OverviewController::class, 'index'])->name('overview.index');
            Route::get('/overview/data', [\App\Http\Controllers\Admin\CBT\OverviewController::class, 'data'])->name('overview.data');
            Route::get('/overview/change-passcode-app', [\App\Http\Controllers\Admin\CBT\OverviewController::class, 'changePasscodeApp'])->name('overview.change-passcode');

            // Bank Soal
            Route::get('/bank-soal/{id}/export', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'exportSoal'])->name('bank-soal.export');
            Route::post('/bank-soal/{id}/import-excel', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'importSoalExcel'])->name('bank-soal.import-excel');
            Route::post('/bank-soal/{id}/import-word', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'importSoalWord'])->name('bank-soal.import-word');
            Route::get('/bank-soal/template-excel', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'downloadTemplateExcel'])->name('bank-soal.download-template-excel');
            Route::get('/bank-soal/template-word', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'downloadTemplateWord'])->name('bank-soal.download-template-word');
            Route::get('/bank-soal/{id}/soal', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'soal'])->name('bank-soal.editor');
            Route::get('/bank-soal/{id}/get-soal', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'getSoal'])->name('bank-soal.get-soal');
            Route::post('/bank-soal/{id}/save-soal', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'saveSoal'])->name('bank-soal.save-soal');
            Route::delete('/bank-soal/{id}/soal/{soalId}', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'hapusSoal'])->name('bank-soal.hapus-soal');
            Route::post('/upload-image', [\App\Http\Controllers\Admin\CBT\BankSoalController::class, 'uploadImage'])->name('bank-soal.upload-image');
            Route::resource('bank-soal', \App\Http\Controllers\Admin\CBT\BankSoalController::class);

            // Draft Ujian
            Route::get('/draft-ujian/{id}/atur-soal', [\App\Http\Controllers\Admin\CBT\DraftUjianController::class, 'aturSoal'])->name('draft-ujian.atur-soal');
            Route::post('/draft-ujian/{id}/save-soal', [\App\Http\Controllers\Admin\CBT\DraftUjianController::class, 'saveSoal'])->name('draft-ujian.save-soal');
            Route::get('/draft-ujian/{id}/cetak', [\App\Http\Controllers\Admin\CBT\DraftUjianController::class, 'cetak'])->name('draft-ujian.cetak');
            Route::resource('draft-ujian', \App\Http\Controllers\Admin\CBT\DraftUjianController::class);
            
            // Jadwal Ujian
            Route::get('/jadwal-ujian/all', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'all'])->name('jadwal-ujian.all');
            Route::get('/jadwal-ujian/master-kelas', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'getMasterKelas'])->name('jadwal-ujian.master-kelas');
            Route::get('/jadwal-ujian/{id}/master-siswa', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'getMasterSiswa'])->name('jadwal-ujian.master-siswa');
            Route::get('/jadwal-ujian/{id}/peserta', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'getPeserta'])->name('jadwal-ujian.peserta');
            Route::put('/jadwal-ujian/{id}/sync-peserta', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'syncPeserta'])->name('jadwal-ujian.sync-peserta');
            Route::post('/jadwal-ujian/{id}/generate', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'generateSoal'])->name('jadwal-ujian.generate');
            Route::post('/jadwal-ujian/{id}/generate-single', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'generateSoalSingle'])->name('jadwal-ujian.generate_single');
            Route::post('/jadwal-ujian/batch-time', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'batchTime'])->name('jadwal-ujian.batch_time');
            Route::post('/jadwal-ujian/hapus-jawaban-masal', [\App\Http\Controllers\Admin\CBT\JadwalUjianController::class, 'hapusJawabanMasal'])->name('jadwal-ujian.hapus_jawaban_masal');
            Route::get('/jadwal-ujian/{id}/print-hadir', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'printHadir'])->name('jadwal-ujian.print_hadir');
            Route::get('/jadwal-ujian/{id}/print-berita', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'printBerita'])->name('jadwal-ujian.print_berita');
            Route::get('/jadwal-ujian/{id}/print-nilai', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'printNilai'])->name('jadwal-ujian.print_nilai');
            Route::get('/jadwal-ujian/{id}/export-excel', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'exportExcel'])->name('jadwal-ujian.export_excel');
            Route::get('/jadwal-ujian/{id}/print-jawaban', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'printJawaban'])->name('jadwal-ujian.print_jawaban');
            Route::get('/jadwal-ujian/{id}/detail', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'index'])->name('jadwal-ujian.detail');
            Route::get('/jadwal-ujian/{id}/status', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'status'])->name('jadwal-ujian.status');
            Route::post('/jadwal-ujian/{id}/progress', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'progress'])->name('jadwal-ujian.progress');
            Route::post('/jadwal-ujian/{id}/token', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'token'])->name('jadwal-ujian.token');
            Route::post('/jadwal-ujian/{id}/extra_time', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'extra_time'])->name('jadwal-ujian.extra_time');
            Route::post('/jadwal-ujian/{jid}/unlock/{ujian_id}', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'unlock'])->name('jadwal-ujian.unlock');
            Route::post('/jadwal-ujian/{id}/unlock_batch', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'unlock_batch'])->name('jadwal-ujian.unlock_batch');
            Route::put('/jadwal-ujian/{id}/finish-batch', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'finish_batch'])->name('jadwal-ujian.finish-batch');
            Route::get('/jadwal-ujian/{id}/jawaban/{ujian_id?}', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'jawaban'])->name('jadwal-ujian.jawaban');
            Route::put('/jadwal-ujian/{id}/jawaban/{ujian_id}', [\App\Http\Controllers\Admin\CBT\DetailJadwalController::class, 'update_jawaban'])->name('jadwal-ujian.update_jawaban');
            Route::resource('jadwal-ujian', \App\Http\Controllers\Admin\CBT\JadwalUjianController::class);
        });
    });

    // App Setting and Role Group
    Route::post('/settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme');
    Route::resource('setting', SettingController::class);

    // Siswa CBT (Computer Based Test) Group
    Route::prefix('siswa/ujian')->name('siswa.ujian.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Siswa\UjianController::class, 'index'])->name('index');
        Route::get('/{id}/konfirmasi', [\App\Http\Controllers\Siswa\UjianController::class, 'konfirmasi'])->name('konfirmasi');
        Route::post('/mulai', [\App\Http\Controllers\Siswa\UjianController::class, 'mulai'])->name('mulai');
        Route::get('/{id}/kerjakan', [\App\Http\Controllers\Siswa\UjianController::class, 'kerjakan'])->name('kerjakan');
        Route::post('/simpan-jawaban', [\App\Http\Controllers\Siswa\UjianController::class, 'simpanJawaban'])->name('simpan-jawaban');
        Route::post('/selesai', [\App\Http\Controllers\Siswa\UjianController::class, 'selesaiUjian'])->name('selesai');
        Route::post('/pelanggaran', [\App\Http\Controllers\Siswa\UjianController::class, 'catatPelanggaran'])->name('pelanggaran');
        Route::get('/{id}/locked', [\App\Http\Controllers\Siswa\UjianController::class, 'locked'])->name('locked');
    });

    // Siswa Presensi Group
    Route::prefix('siswa/presensi')->name('siswa.presensi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Siswa\PresensiController::class, 'index'])->name('index');
        Route::get('/absen-harian', [\App\Http\Controllers\Siswa\PresensiController::class, 'absenHarian'])->name('absen_harian');
        Route::post('/submit-absen', [\App\Http\Controllers\Siswa\PresensiController::class, 'submitAbsen'])->name('submit_absen');
        Route::get('/izin', [\App\Http\Controllers\Siswa\PresensiController::class, 'izin'])->name('izin');
        Route::post('/ajukan', [\App\Http\Controllers\Siswa\PresensiController::class, 'ajukan'])->name('ajukan');
        Route::get('/rekap', [\App\Http\Controllers\Siswa\PresensiController::class, 'rekap'])->name('rekap');
        Route::get('/cetak-rekap', [\App\Http\Controllers\Siswa\PresensiController::class, 'cetakRekap'])->name('cetak_rekap');
    });

    // Guru Presensi Group
    Route::prefix('guru/presensi')->name('guru.presensi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\PresensiController::class, 'index'])->name('index');
        Route::get('/absen-harian', [\App\Http\Controllers\Guru\PresensiController::class, 'absenHarian'])->name('absen_harian');
        Route::post('/submit-absen', [\App\Http\Controllers\Guru\PresensiController::class, 'submitAbsen'])->name('submit_absen');
        Route::get('/izin', [\App\Http\Controllers\Guru\PresensiController::class, 'izin'])->name('izin');
        Route::post('/ajukan', [\App\Http\Controllers\Guru\PresensiController::class, 'ajukan'])->name('ajukan');
        Route::get('/rekap', [\App\Http\Controllers\Guru\PresensiController::class, 'rekap'])->name('rekap');
        Route::get('/cetak-rekap', [\App\Http\Controllers\Guru\PresensiController::class, 'cetakRekap'])->name('cetak_rekap');
    });
});

Route::get('/test-jadwal-kelas/{id}', function ($id) {
    $count = \App\Models\UjianSiswa::where('jadwal_id', $id)->count();
    $peserta = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
        ->where('us.jadwal_id', $id)
        ->get();
        
    $joined = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
        ->leftJoin('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
        ->leftJoin('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
        ->where('us.jadwal_id', $id)
        ->select('us.siswa_id', 'ms.kelas_id', 'mk.nama_kelas')
        ->get();
        
    $kelas_concat = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
        ->join('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
        ->join('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
        ->where('us.jadwal_id', $id)
        ->selectRaw("GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas")
        ->value('kelas');
        
    return response()->json([
        'count' => $count,
        'peserta' => $peserta,
        'joined' => $joined,
        'kelas_concat' => $kelas_concat
    ]);
});

// Load standard Auth routes (Login, Register, etc.)
require __DIR__.'/auth.php';

// Public Surat Verification
Route::get('/verifikasi/{token}', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'verifikasi'])->name('surat.verifikasi');
Route::get('/cetak-surat/{id}', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'cetak'])->name('surat.cetak')->middleware('auth');

// API Routes are now served via routes/api.php and configured in bootstrap/app.php

// Temporary Route to Fix CI4 User ID Mapping
Route::get('/fix-users', function () {
    \Illuminate\Support\Facades\DB::statement('UPDATE tbl_guru SET user_id = id_user WHERE id_user IS NOT NULL AND id_user > 0');
    \Illuminate\Support\Facades\DB::statement('UPDATE tbl_siswa SET user_id = id_user WHERE id_user IS NOT NULL AND id_user > 0');
    
    // Hapus sisa user duplikat (auto-heal) yang barusan terbuat (yang role aslinya belum lengkap)
    // agar database kembali bersih seperti sedia kala (opsional tapi disarankan)
    // Untuk amannya, kita biarkan saja. Yang penting user_id sudah kembali nyantol ke id_user lama.
    
    return 'Berhasil menyinkronkan data User ID lama (CI4) ke sistem baru! Silakan kembali ke menu Manajemen Guru di web, refresh halaman, dan role lama pasti sudah muncul semua!';
});