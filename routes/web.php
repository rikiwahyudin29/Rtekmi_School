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

// Public Pages
Route::get('/sitemap.xml', function () {
    $berita = \Illuminate\Support\Facades\DB::table('tbl_berita')->where('is_published', 1)->orderBy('created_at', 'desc')->get();
    return response()->view('sitemap', ['berita' => $berita])->header('Content-Type', 'text/xml');
});

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Portal Berita Publik
Route::get('/berita', [\App\Http\Controllers\BeritaPublicController::class, 'index'])->name('public.berita.index');
Route::get('/berita/{slug}', [\App\Http\Controllers\BeritaPublicController::class, 'show'])->name('public.berita.show');

// Portal PPDB Publik
Route::get('/spmb/register', [\App\Http\Controllers\PpdbPublicController::class, 'create'])->name('public.ppdb.create');
Route::post('/spmb/register', [\App\Http\Controllers\PpdbPublicController::class, 'store'])->name('public.ppdb.store');
Route::get('/spmb/success', [\App\Http\Controllers\PpdbPublicController::class, 'success'])->name('public.ppdb.success');

// API.CO.ID Proxy
Route::get('/api/sekolah/search', [\App\Http\Controllers\ApiController::class, 'searchSekolah'])->name('api.sekolah.search');
Route::get('/api/regional/provinces', [\App\Http\Controllers\ApiController::class, 'getProvinces'])->name('api.regional.provinces');
Route::get('/api/regional/regencies/{province}', [\App\Http\Controllers\ApiController::class, 'getRegencies'])->name('api.regional.regencies');
Route::get('/api/regional/districts/{regency}', [\App\Http\Controllers\ApiController::class, 'getDistricts'])->name('api.regional.districts');
Route::get('/api/regional/villages/{district}', [\App\Http\Controllers\ApiController::class, 'getVillages'])->name('api.regional.villages');
Route::get('/api/holidays', [\App\Http\Controllers\ApiController::class, 'getHolidays'])->name('api.holidays');

Route::get('/verifikasi/{token}', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'verifikasi'])->name('surat.verifikasi');
Route::get('/verifikasi/{token}/cetak', [\App\Http\Controllers\Admin\Surat\SuratKeluarController::class, 'cetakPublic'])->name('surat.verifikasi.cetak');

// Cek Kelulusan
Route::get('/cek-kelulusan', [\App\Http\Controllers\Front\KelulusanController::class, 'index'])->name('kelulusan.index');
Route::post('/cek-kelulusan', [\App\Http\Controllers\Front\KelulusanController::class, 'cek'])->name('kelulusan.cek');
Route::post('/cek-kelulusan/hasil', [\App\Http\Controllers\Front\KelulusanController::class, 'getHasil'])->name('kelulusan.hasil');
Route::get('/cek-kelulusan/download', [\App\Http\Controllers\Front\KelulusanController::class, 'downloadDokumen'])->name('kelulusan.download');

// Tracer Study (Alumni)
Route::get('/tracer', [\App\Http\Controllers\Front\TracerFrontController::class, 'index'])->name('tracer.index');
Route::post('/tracer/cek-nisn', [\App\Http\Controllers\Front\TracerFrontController::class, 'cekNisn'])->name('tracer.cek_nisn');
Route::post('/tracer', [\App\Http\Controllers\Front\TracerFrontController::class, 'simpan'])->name('tracer.simpan');
// Public PPDB Routes
Route::get('/ppdb', [\App\Http\Controllers\Public\PpdbPublicController::class, 'index'])->name('ppdb.index');
Route::get('/ppdb/pendaftaran', [\App\Http\Controllers\Public\PpdbPublicController::class, 'pendaftaran'])->name('ppdb.pendaftaran');
Route::post('/ppdb/pendaftaran', [\App\Http\Controllers\Public\PpdbPublicController::class, 'storePendaftaran'])->name('ppdb.pendaftaran.store');
Route::get('/ppdb/cek-status', [\App\Http\Controllers\Public\PpdbPublicController::class, 'cekStatus'])->name('ppdb.cek-status');

// Public Buku Tamu
Route::get('/buku-tamu', [\App\Http\Controllers\Public\BukuTamuController::class, 'index'])->name('buku-tamu.index');
Route::post('/buku-tamu', [\App\Http\Controllers\Public\BukuTamuController::class, 'store'])->name('buku-tamu.store');
Route::get('/buku-tamu/{id}/cetak', [\App\Http\Controllers\Public\BukuTamuController::class, 'cetak'])->name('buku-tamu.cetak');
Route::get('/kelulusan/hasil', [\App\Http\Controllers\Public\PortalKelulusanController::class, 'getHasil'])->name('kelulusan.hasil');
Route::get('/kelulusan/antrian/request', [\App\Http\Controllers\Public\PortalKelulusanController::class, 'requestAntrian'])->name('kelulusan.antrian.request');
Route::get('/kelulusan/antrian/cek/{id}', [\App\Http\Controllers\Public\PortalKelulusanController::class, 'cekAntrian'])->name('kelulusan.antrian.cek');
Route::get('/kelulusan/download', [\App\Http\Controllers\Public\PortalKelulusanController::class, 'downloadDokumen'])->name('kelulusan.download');

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
Route::get('/debug-jadwal', function() {
    return \App\Models\JadwalPelajaran::all();
});

Route::get('/debug-jam', function() {
    return \App\Models\JamMaster::all();
});

Route::get('/debug-sidebar', function() {
    $lines = file('c:\\Users\\Rik\\Documents\\Web\\siakad rj\\app\\Views\\layout\\sidebar.php');
    return implode("", array_slice($lines, 500, 30));
});

Route::get('/clear-all', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Caches cleared! Please go back and hard refresh (Ctrl+F5).';
});

Route::get('/recreate-tbl-formatif', function() {
    try {
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS tbl_nilai_formatif');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE tbl_nilai_formatif (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            mapel_id INT NOT NULL,
            tp_id INT NOT NULL,
            tahun_ajaran_id INT DEFAULT NULL,
            nilai DOUBLE(8, 2) DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL
        )');
        
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS tbl_nilai_sumatif');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE tbl_nilai_sumatif (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            mapel_id INT NOT NULL,
            guru_id INT NOT NULL,
            siswa_id INT NOT NULL,
            jenis VARCHAR(255) NOT NULL,
            nilai DOUBLE(8, 2) DEFAULT 0,
            semester INT NOT NULL,
            tahun_ajaran_id INT NOT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL
        )');
        
        return 'Tables recreated successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/fix-db', function() {
    try {
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS tbl_nilai_formatif');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE tbl_nilai_formatif (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            mapel_id INT NOT NULL,
            tp_id INT NOT NULL,
            tahun_ajaran_id INT DEFAULT NULL,
            nilai DOUBLE(8, 2) DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL
        )');
        return 'Table tbl_nilai_formatif recreated successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // D. Admin Monitoring Rapor
    Route::get('/admin/monitoring/rapor', [\App\Http\Controllers\Admin\MonitoringRaporController::class, 'index'])->name('admin.monitoring.rapor');

    // E. Admin P5 Management
    Route::prefix('admin/p5')->name('admin.p5.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\P5Controller::class, 'index'])->name('index');
        Route::post('/tema', [\App\Http\Controllers\Admin\P5Controller::class, 'storeTema'])->name('tema.store');
        Route::get('/kelompok', [\App\Http\Controllers\Admin\P5Controller::class, 'kelompok'])->name('kelompok.index');
        Route::post('/kelompok', [\App\Http\Controllers\Admin\P5Controller::class, 'storeKelompok'])->name('kelompok.store');
    });

    // F. Admin Kokurikuler
    Route::prefix('admin/kokurikuler')->name('admin.kokurikuler.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\KokurikulerController::class, 'index'])->name('index');
        Route::post('/tema', [\App\Http\Controllers\Admin\KokurikulerController::class, 'storeTema'])->name('tema.store');
        Route::post('/kegiatan', [\App\Http\Controllers\Admin\KokurikulerController::class, 'storeKegiatan'])->name('kegiatan.store');
        Route::post('/kelompok', [\App\Http\Controllers\Admin\KokurikulerController::class, 'storeKelompok'])->name('kelompok.store');
    });

    // Admin Ekskul
    Route::prefix('admin/ekskul')->name('admin.ekskul.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\EkskulController::class, 'index'])->name('index');
        Route::post('/simpan', [\App\Http\Controllers\Admin\EkskulController::class, 'simpan'])->name('simpan');
        Route::post('/update', [\App\Http\Controllers\Admin\EkskulController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [\App\Http\Controllers\Admin\EkskulController::class, 'delete'])->name('delete');
        Route::post('/tambah-pembina', [\App\Http\Controllers\Admin\EkskulController::class, 'tambahPembina'])->name('tambah_pembina');
        Route::delete('/hapus-pembina/{id}', [\App\Http\Controllers\Admin\EkskulController::class, 'hapusPembina'])->name('hapus_pembina');
    });

    // I. Admin Keuangan
    Route::prefix('admin/keuangan')->name('admin.keuangan.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Admin\Keuangan\DashboardController::class, 'index'])->name('dashboard');
        
        // Tabungan / Bank Mini
        Route::get('tabungan', [\App\Http\Controllers\Admin\Keuangan\TabunganController::class, 'index'])->name('tabungan.index');
        Route::post('tabungan/buka-rekening', [\App\Http\Controllers\Admin\Keuangan\TabunganController::class, 'store'])->name('tabungan.store');
        Route::get('tabungan/detail/{id}', [\App\Http\Controllers\Admin\Keuangan\TabunganController::class, 'show'])->name('tabungan.show');
        Route::post('tabungan/transaksi', [\App\Http\Controllers\Admin\Keuangan\TabunganController::class, 'prosesTransaksi'])->name('tabungan.transaksi');
    });

    // Admin PKL
    Route::prefix('admin/pkl')->name('admin.pkl.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\PklController::class, 'dashboard'])->name('dashboard');
        Route::post('/set-kelas-pkl', [\App\Http\Controllers\Admin\PklController::class, 'set_kelas_pkl'])->name('set_kelas_pkl');

        Route::get('/', [\App\Http\Controllers\Admin\PklController::class, 'index'])->name('index');
        Route::post('/dudi/simpan', [\App\Http\Controllers\Admin\PklController::class, 'simpanDudi'])->name('dudi.simpan');
        Route::post('/dudi/update', [\App\Http\Controllers\Admin\PklController::class, 'updateDudi'])->name('dudi.update');
        Route::delete('/dudi/delete/{id}', [\App\Http\Controllers\Admin\PklController::class, 'deleteDudi'])->name('dudi.delete');
        
        Route::get('/kelompok', [\App\Http\Controllers\Admin\PklController::class, 'kelompok'])->name('kelompok');
        Route::post('/kelompok/simpan', [\App\Http\Controllers\Admin\PklController::class, 'storeKelompok'])->name('kelompok.simpan');
    });

    // F. Admin UKK Management
    Route::prefix('admin/ukk')->name('admin.ukk.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UkkController::class, 'index'])->name('index');
        Route::post('/skkni', [\App\Http\Controllers\Admin\UkkController::class, 'storeSkkni'])->name('skkni.store');
        Route::post('/asesor', [\App\Http\Controllers\Admin\UkkController::class, 'storeAsesor'])->name('asesor.store');
        
        Route::get('/nilai', [\App\Http\Controllers\Admin\UkkController::class, 'nilai'])->name('nilai');
        Route::post('/nilai', [\App\Http\Controllers\Admin\UkkController::class, 'storeNilai'])->name('nilai.store');
        
        Route::get('/kuk', [\App\Http\Controllers\Admin\UkkController::class, 'kuk'])->name('kuk');
        Route::post('/kuk', [\App\Http\Controllers\Admin\UkkController::class, 'storeKuk'])->name('kuk.store');

        Route::get('/skill-passport', [\App\Http\Controllers\Admin\UkkController::class, 'skillPassport'])->name('skill_passport');
        Route::post('/skill-passport', [\App\Http\Controllers\Admin\UkkController::class, 'storeSkillPassport'])->name('skill_passport.store');
    });

    // G. Admin PKL Management
    Route::prefix('admin/pkl')->name('admin.pkl.')->group(function () {
        Route::get('/kelompok', [\App\Http\Controllers\Admin\PklController::class, 'kelompok'])->name('kelompok');
        Route::post('/kelompok', [\App\Http\Controllers\Admin\PklController::class, 'storeKelompok'])->name('kelompok.store');
        Route::put('/kelompok/{id}', [\App\Http\Controllers\Admin\PklController::class, 'updateKelompok'])->name('kelompok.update');
        Route::delete('/kelompok/{id}', [\App\Http\Controllers\Admin\PklController::class, 'deleteKelompok'])->name('kelompok.destroy');
    });

    // H. Admin Transkrip Ijazah
    Route::prefix('admin/ijazah')->name('admin.ijazah.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\IjazahController::class, 'index'])->name('index');
        Route::post('/data', [\App\Http\Controllers\Admin\IjazahController::class, 'storeDataIjazah'])->name('data.store');
    });

    // J. Admin Sarpras (Aset & Fasilitas)
    Route::prefix('admin/sarpras')->name('admin.sarpras.')->group(function () {
        // Inventaris
        Route::get('inventaris/print', [\App\Http\Controllers\Admin\Sarpras\InventarisController::class, 'printLabel'])->name('inventaris.print');
        Route::get('inventaris/pdf', [\App\Http\Controllers\Admin\Sarpras\InventarisController::class, 'exportPdf'])->name('inventaris.pdf');
        Route::resource('inventaris', \App\Http\Controllers\Admin\Sarpras\InventarisController::class)->except(['create', 'show', 'edit']);
        
        // Peminjaman
        Route::get('peminjaman', [\App\Http\Controllers\Admin\Sarpras\PeminjamanRuanganController::class, 'index'])->name('peminjaman.index');
        Route::post('peminjaman', [\App\Http\Controllers\Admin\Sarpras\PeminjamanRuanganController::class, 'store'])->name('peminjaman.store');
        Route::put('peminjaman/{id}/status', [\App\Http\Controllers\Admin\Sarpras\PeminjamanRuanganController::class, 'updateStatus'])->name('peminjaman.update-status');
        Route::delete('peminjaman/{id}', [\App\Http\Controllers\Admin\Sarpras\PeminjamanRuanganController::class, 'destroy'])->name('peminjaman.destroy');
        
        // Kerusakan
        Route::get('kerusakan', [\App\Http\Controllers\Admin\Sarpras\LaporanKerusakanController::class, 'index'])->name('kerusakan.index');
        Route::post('kerusakan', [\App\Http\Controllers\Admin\Sarpras\LaporanKerusakanController::class, 'store'])->name('kerusakan.store');
        Route::put('kerusakan/{id}/status', [\App\Http\Controllers\Admin\Sarpras\LaporanKerusakanController::class, 'updateStatus'])->name('kerusakan.update-status');
        Route::delete('kerusakan/{id}', [\App\Http\Controllers\Admin\Sarpras\LaporanKerusakanController::class, 'destroy'])->name('kerusakan.destroy');
    });

    // --- 7. PERPUSTAKAAN ---
    Route::prefix('admin/perpus')->name('admin.perpus.')->group(function () {
        Route::get('katalog', [\App\Http\Controllers\Admin\PerpustakaanController::class, 'katalog'])->name('katalog');
        Route::post('simpan-buku', [\App\Http\Controllers\Admin\PerpustakaanController::class, 'simpanBuku'])->name('simpan-buku');
        Route::delete('hapus-buku/{id}', [\App\Http\Controllers\Admin\PerpustakaanController::class, 'hapusBuku'])->name('hapus-buku');
        
        Route::get('sirkulasi', [\App\Http\Controllers\Admin\PerpustakaanController::class, 'sirkulasi'])->name('sirkulasi');
        Route::post('proses-pinjam', [\App\Http\Controllers\Admin\PerpustakaanController::class, 'prosesPinjam'])->name('proses-pinjam');
        Route::post('proses-kembali/{id}', [\App\Http\Controllers\Admin\PerpustakaanController::class, 'prosesKembali'])->name('proses-kembali');
    });

    // I. Admin Perkembangan Nilai
    Route::get('perkembangan-nilai', [\App\Http\Controllers\Admin\PerkembanganNilaiController::class, 'index'])->name('admin.perkembangan-nilai.index');

    // Guru Group
    Route::prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\GuruController::class, 'dashboard'])->name('dashboard');
        // KBM & Penilaian
        Route::get('jadwal-mengajar', [\App\Http\Controllers\Guru\JadwalMengajarController::class, 'index'])->name('jadwal-mengajar.index');
        Route::get('penilaian/tp', [\App\Http\Controllers\Guru\PenilaianController::class, 'tp'])->name('penilaian.tp');
        Route::get('penilaian/formatif', [\App\Http\Controllers\Guru\PenilaianController::class, 'formatif'])->name('penilaian.formatif');
        Route::get('penilaian/sumatif', [\App\Http\Controllers\Guru\PenilaianController::class, 'sumatif'])->name('penilaian.sumatif');
        Route::get('penilaian/sikap-k13', [\App\Http\Controllers\Guru\PenilaianController::class, 'sikapK13'])->name('penilaian.sikap_k13');
        Route::get('penilaian/generate-nilai-akhir', [\App\Http\Controllers\Guru\PenilaianController::class, 'halamanGenerateNilaiAkhir'])->name('penilaian.halaman_generate_nilai_akhir');
        
        // Tugas Tambahan
        // Route::resource('walikelas', \App\Http\Controllers\Guru\WalikelasController::class);
        Route::resource('ekskul', \App\Http\Controllers\Guru\EkskulController::class);
        Route::prefix('ekskul')->name('ekskul.')->group(function () {
            Route::get('anggota/{id_ekskul}', [\App\Http\Controllers\Guru\EkskulController::class, 'anggota'])->name('anggota');
            Route::post('anggota/validasi', [\App\Http\Controllers\Guru\EkskulController::class, 'validasiAnggota'])->name('anggota.validasi');
            Route::get('anggota/calon/{ekskul_id}', [\App\Http\Controllers\Guru\EkskulController::class, 'getCalonAnggota'])->name('anggota.calon');
            Route::post('anggota/tambah-manual', [\App\Http\Controllers\Guru\EkskulController::class, 'tambahManual'])->name('anggota.tambah_manual');
            
            Route::get('jurnal/{id}', [\App\Http\Controllers\Guru\EkskulController::class, 'jurnal'])->name('jurnal');
            Route::post('jurnal/simpan', [\App\Http\Controllers\Guru\EkskulController::class, 'jurnalSimpan'])->name('jurnal.simpan');
            
            Route::get('prestasi/{id}', [\App\Http\Controllers\Guru\EkskulController::class, 'prestasi'])->name('prestasi');
            Route::post('prestasi/simpan', [\App\Http\Controllers\Guru\EkskulController::class, 'prestasiSimpan'])->name('prestasi.simpan');
            
            Route::get('penilaian/{id}', [\App\Http\Controllers\Guru\EkskulController::class, 'penilaian'])->name('penilaian');
            Route::post('penilaian/simpan', [\App\Http\Controllers\Guru\EkskulController::class, 'penilaianSimpan'])->name('penilaian.simpan');
            Route::get('penilaian/cetak/{id}', [\App\Http\Controllers\Guru\EkskulController::class, 'cetakSertifikat'])->name('penilaian.cetak');
            
            Route::get('scanner/{id}', [\App\Http\Controllers\Guru\EkskulController::class, 'absenScan'])->name('scanner');
            Route::post('scanner/proses', [\App\Http\Controllers\Guru\EkskulController::class, 'prosesScan'])->name('scanner.proses');
            Route::post('absen/manual', [\App\Http\Controllers\Guru\EkskulController::class, 'absenManual'])->name('absen.manual');
        });
        Route::resource('p5', \App\Http\Controllers\Guru\P5Controller::class);
        Route::resource('kokurikuler', \App\Http\Controllers\Guru\KokurikulerController::class);
        Route::prefix('pkl')->name('pkl.')->group(function () {
            Route::get('monitoring', [\App\Http\Controllers\Guru\PklController::class, 'monitoring'])->name('monitoring');
            Route::get('monitoring/cetak', [\App\Http\Controllers\Guru\PklController::class, 'cetakMonitoring'])->name('monitoring.cetak');
            
            Route::get('jurnal', [\App\Http\Controllers\Guru\PklController::class, 'jurnal'])->name('jurnal');
            Route::post('jurnal/validasi', [\App\Http\Controllers\Guru\PklController::class, 'jurnalValidasi'])->name('jurnal.validasi');
            Route::post('laporan/validasi', [\App\Http\Controllers\Guru\PklController::class, 'laporanValidasi'])->name('laporan.validasi');
            
            Route::get('kunjungan', [\App\Http\Controllers\Guru\PklController::class, 'kunjungan'])->name('kunjungan');
            Route::post('kunjungan', [\App\Http\Controllers\Guru\PklController::class, 'kunjunganSimpan'])->name('kunjungan.simpan');
            
            Route::get('nilai', [\App\Http\Controllers\Guru\PklController::class, 'nilai'])->name('nilai');
            Route::post('nilai', [\App\Http\Controllers\Guru\PklController::class, 'simpanNilai'])->name('nilai.simpan');
            Route::get('sertifikat/{id}', [\App\Http\Controllers\Guru\PklController::class, 'cetakSertifikat'])->name('sertifikat.cetak');
        });
        Route::resource('pkl', \App\Http\Controllers\Guru\PklController::class);
        
        // Guru Piket
        Route::get('/piket/dashboard', [\App\Http\Controllers\Guru\PiketController::class, 'dashboard'])->name('piket.dashboard');
        Route::resource('piket/buku-tamu', \App\Http\Controllers\Guru\PiketBukuTamuController::class)->names('piket.buku-tamu');
        Route::patch('piket/buku-tamu/{id}/pulang', [\App\Http\Controllers\Guru\PiketBukuTamuController::class, 'pulang'])->name('piket.buku-tamu.pulang');
        
        // Presensi & Layanan Mandiri Guru
        Route::get('presensi', [\App\Http\Controllers\Guru\PresensiController::class, 'index'])->name('presensi.index');
        Route::get('presensi/absen-harian', [\App\Http\Controllers\Guru\PresensiController::class, 'absenHarian'])->name('presensi.absen_harian');
        Route::get('presensi/rekap', [\App\Http\Controllers\Guru\PresensiController::class, 'rekap'])->name('presensi.rekap');
        Route::get('presensi/izin', [\App\Http\Controllers\Guru\PresensiController::class, 'izin'])->name('presensi.izin');
    });

    // --- BK AREA ---
    Route::prefix('bk')->name('bk.')->group(function () {
        // Pelanggaran / Buku Kasus
        Route::get('pelanggaran/pdf', [\App\Http\Controllers\Bk\PelanggaranController::class, 'exportPdf'])->name('pelanggaran.pdf');
        Route::get('pelanggaran/rekap', [\App\Http\Controllers\Bk\PelanggaranController::class, 'rekapSiswa'])->name('pelanggaran.rekap');
        Route::get('pelanggaran', [\App\Http\Controllers\Bk\PelanggaranController::class, 'index'])->name('pelanggaran.index');
        Route::post('pelanggaran/store', [\App\Http\Controllers\Bk\PelanggaranController::class, 'store'])->name('pelanggaran.store');
        Route::post('pelanggaran/sp', [\App\Http\Controllers\Bk\PelanggaranController::class, 'updateSp'])->name('pelanggaran.sp.update');
        Route::delete('pelanggaran/destroy/{id}', [\App\Http\Controllers\Bk\PelanggaranController::class, 'destroy'])->name('pelanggaran.destroy');

        // Master Pelanggaran
        Route::get('master-pelanggaran', [\App\Http\Controllers\Bk\MasterPelanggaranController::class, 'index'])->name('master-pelanggaran.index');
        Route::post('master-pelanggaran', [\App\Http\Controllers\Bk\MasterPelanggaranController::class, 'store'])->name('master-pelanggaran.store');
        Route::put('master-pelanggaran/{id}', [\App\Http\Controllers\Bk\MasterPelanggaranController::class, 'update'])->name('master-pelanggaran.update');
        Route::delete('master-pelanggaran/{id}', [\App\Http\Controllers\Bk\MasterPelanggaranController::class, 'destroy'])->name('master-pelanggaran.destroy');

        // Catatan Konseling
        Route::get('konseling', [\App\Http\Controllers\Bk\KonselingController::class, 'index'])->name('konseling.index');
        Route::post('konseling', [\App\Http\Controllers\Bk\KonselingController::class, 'store'])->name('konseling.store');
        Route::put('konseling/{id}', [\App\Http\Controllers\Bk\KonselingController::class, 'update'])->name('konseling.update');
        Route::delete('konseling/{id}', [\App\Http\Controllers\Bk\KonselingController::class, 'destroy'])->name('konseling.destroy');
    });

    // Admin Group
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Admin Master Group
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('tahun-ajaran', TahunAjaranController::class);
            Route::resource('jurusan', JurusanController::class);
            Route::resource('kelas', KelasController::class);
            Route::get('mapel/template', [MapelController::class, 'template'])->name('mapel.template');
            Route::post('mapel/import', [MapelController::class, 'import'])->name('mapel.import');
            Route::resource('mapel', MapelController::class);
            Route::resource('ruangan', RuanganController::class);
            Route::resource('jenis-ujian', JenisUjianController::class);
            Route::resource('ekskul', \App\Http\Controllers\Admin\Master\EkskulController::class)->except(['create', 'edit', 'show']);
            
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
            Route::resource('backup', BackupController::class)->only(['index']);

            // Dapodik specific routes
            Route::post('dapodik/update', [DapodikController::class, 'update'])->name('dapodik.update');
            Route::post('dapodik/test', [DapodikController::class, 'testConnection'])->name('dapodik.test');
            Route::post('dapodik/sync/{type}', [DapodikController::class, 'sync'])->name('dapodik.sync');
            Route::resource('dapodik', DapodikController::class)->only(['index']);
        });

        // Admin User/Data Group
        Route::post('guru/{id}/roles', [GuruController::class, 'syncRoles'])->name('guru.roles.sync');
        Route::post('guru/{id}/reset-password', [GuruController::class, 'resetPassword'])->name('guru.reset-password');
        Route::post('guru/{id}/reset-2fa', [GuruController::class, 'reset2FA'])->name('guru.reset2fa');
        Route::resource('guru', GuruController::class);
        Route::post('siswa/{id}/reset-password', [SiswaController::class, 'resetPassword'])->name('siswa.reset-password');
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
            Route::post('jadwal-pelajaran/auto-generate', [JadwalPelajaranController::class, 'autoGenerate'])->name('jadwal-pelajaran.auto-generate');
            Route::delete('jadwal-pelajaran/clear-all', [JadwalPelajaranController::class, 'clearAll'])->name('jadwal-pelajaran.clear-all');
            
            Route::get('debug-log', function() {
                $path = storage_path('logs/laravel.log');
                if (!file_exists($path)) return 'No log file';
                $lines = file($path);
                $filtered = array_filter($lines, function($line) {
                    return strpos($line, 'MULAI AUTO GENERATE') !== false || 
                           strpos($line, 'Memproses Mapel') !== false || 
                           strpos($line, 'Inserted 1 JP') !== false;
                });
                return '<pre>' . implode("", array_slice($filtered, -100)) . '</pre>';
            });

            Route::resource('jadwal-pelajaran', JadwalPelajaranController::class)->except(['create', 'edit', 'show']);
        });

        // Admin Presensi Group
        Route::prefix('presensi')->name('presensi.')->group(function () {
            Route::get('scanner', [\App\Http\Controllers\Admin\PresensiController::class, 'scanner'])->name('scanner');
            Route::post('proses-scan', [\App\Http\Controllers\Admin\PresensiController::class, 'prosesScan'])->name('proses_scan');
            Route::get('/hari_libur', [\App\Http\Controllers\Admin\HariLiburController::class, 'index'])->name('hari_libur.index');
            Route::post('/hari_libur', [\App\Http\Controllers\Admin\HariLiburController::class, 'store'])->name('hari_libur.store');
            Route::post('/hari_libur/sync', [\App\Http\Controllers\Admin\HariLiburController::class, 'syncApi'])->name('hari_libur.sync');
            Route::delete('/hari_libur/{id}', [\App\Http\Controllers\Admin\HariLiburController::class, 'destroy'])->name('hari_libur.destroy');
            Route::get('/kartu', [\App\Http\Controllers\Admin\KartuController::class, 'index'])->name('kartu.index');
            Route::get('/kartu/cetak-siswa', [\App\Http\Controllers\Admin\KartuController::class, 'cetakSiswa'])->name('kartu.cetak_siswa');
            Route::get('/kartu/cetak-guru', [\App\Http\Controllers\Admin\KartuController::class, 'cetakGuru'])->name('kartu.cetak_guru');
            Route::post('/kartu/simpan-uid', [\App\Http\Controllers\Admin\KartuController::class, 'simpanUid'])->name('kartu.simpan_uid');
            Route::get('izin', [\App\Http\Controllers\Admin\PresensiController::class, 'izin'])->name('izin');
            Route::post('simpan-izin', [\App\Http\Controllers\Admin\PresensiController::class, 'simpanIzin'])->name('simpan_izin');
            Route::delete('hapus-izin/{id}', [\App\Http\Controllers\Admin\PresensiController::class, 'hapusIzin'])->name('hapus_izin');
            Route::get('/manual', [\App\Http\Controllers\Admin\PresensiController::class, 'manual'])->name('manual');
            Route::get('/get-belum-absen', [\App\Http\Controllers\Admin\PresensiController::class, 'getBelumAbsen'])->name('get_belum_absen');
            Route::post('/simpan-manual', [\App\Http\Controllers\Admin\PresensiController::class, 'simpanManual'])->name('simpan_manual');
            Route::post('/simpan-manual-ajax', [\App\Http\Controllers\Admin\PresensiController::class, 'simpanManualAjax'])->name('simpan_manual_ajax');
            Route::delete('/hapus-manual/{id}', [\App\Http\Controllers\Admin\PresensiController::class, 'hapus_manual'])->name('hapus_manual');
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
        
        // Admin Humas Hubin Group
        Route::prefix('humas')->name('humas.')->group(function () {
            Route::get('buku-tamu/cetak/rekap', [\App\Http\Controllers\Admin\Humas\BukuTamuController::class, 'cetakRekap'])->name('buku-tamu.cetak');
            Route::resource('buku-tamu', \App\Http\Controllers\Admin\Humas\BukuTamuController::class)->except(['create', 'show', 'edit', 'update', 'store']);
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
            Route::get('/cetak-semua-transkrip', [\App\Http\Controllers\Admin\KelulusanController::class, 'cetakTranskripSemua'])->name('cetak_semua_transkrip');
            Route::get('/cetak-semua-skl', [\App\Http\Controllers\Admin\KelulusanController::class, 'cetakSklSemua'])->name('cetak_semua_skl');
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
        // Keuangan Group
        Route::prefix('keuangan')->name('keuangan.')->group(function () {
            // Pos Bayar
            Route::resource('pos', \App\Http\Controllers\Admin\Keuangan\PosBayarController::class)->except(['create', 'edit', 'show']);
            
            // Jenis Bayar
            Route::resource('jenis', \App\Http\Controllers\Admin\Keuangan\JenisBayarController::class)->except(['create', 'edit', 'show']);
            
            // Tagihan
            Route::get('tagihan/kelola/{id_jenis}', [\App\Http\Controllers\Admin\Keuangan\TagihanController::class, 'kelola'])->name('tagihan.kelola');
            Route::post('tagihan/generate', [\App\Http\Controllers\Admin\Keuangan\TagihanController::class, 'generate'])->name('tagihan.generate');
            Route::post('tagihan/update-nominal', [\App\Http\Controllers\Admin\Keuangan\TagihanController::class, 'updateNominal'])->name('tagihan.update_nominal');
            
            // Pembayaran
            Route::get('pembayaran', [\App\Http\Controllers\Admin\Keuangan\PembayaranController::class, 'index'])->name('pembayaran.index');
            Route::get('pembayaran/siswa/{id_siswa}', [\App\Http\Controllers\Admin\Keuangan\PembayaranController::class, 'transaksi'])->name('pembayaran.transaksi');
            Route::post('pembayaran/proses', [\App\Http\Controllers\Admin\Keuangan\PembayaranController::class, 'prosesBayar'])->name('pembayaran.proses_bayar');
            Route::post('pembayaran/batal', [\App\Http\Controllers\Admin\Keuangan\PembayaranController::class, 'batal'])->name('pembayaran.batal');
            Route::get('pembayaran/cetak-thermal/{id}', [\App\Http\Controllers\Admin\Keuangan\PembayaranController::class, 'cetakThermal'])->name('pembayaran.cetak_thermal');
            Route::get('pembayaran/cetak-invoice/{id}', [\App\Http\Controllers\Admin\Keuangan\PembayaranController::class, 'cetakInvoice'])->name('pembayaran.cetak_invoice');
            
            // Pengeluaran
            Route::get('pengeluaran', [\App\Http\Controllers\Admin\Keuangan\PengeluaranController::class, 'index'])->name('pengeluaran.index');
            Route::post('pengeluaran', [\App\Http\Controllers\Admin\Keuangan\PengeluaranController::class, 'store'])->name('pengeluaran.store');
            Route::delete('pengeluaran/{id}', [\App\Http\Controllers\Admin\Keuangan\PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');
            Route::post('pengeluaran/divisi', [\App\Http\Controllers\Admin\Keuangan\PengeluaranController::class, 'storeDivisi'])->name('pengeluaran.store_divisi');
            Route::post('pengeluaran/jenis', [\App\Http\Controllers\Admin\Keuangan\PengeluaranController::class, 'storeJenis'])->name('pengeluaran.store_jenis');

            // Laporan
            Route::get('laporan', [\App\Http\Controllers\Admin\Keuangan\LaporanController::class, 'index'])->name('laporan.index');
            Route::get('laporan/cetak-transaksi', [\App\Http\Controllers\Admin\Keuangan\LaporanController::class, 'cetakTransaksi'])->name('laporan.cetak_transaksi');
            Route::get('laporan/cetak-tunggakan', [\App\Http\Controllers\Admin\Keuangan\LaporanController::class, 'cetakTunggakan'])->name('laporan.cetak_tunggakan');
            Route::get('laporan/export', [\App\Http\Controllers\Admin\Keuangan\LaporanController::class, 'exportExcel'])->name('laporan.export');

            // Log
            Route::get('log-aktivitas', [\App\Http\Controllers\Admin\Keuangan\LogKeuanganController::class, 'index'])->name('log.index');

            // Notif Tagihan
            Route::get('notif-tagihan', [\App\Http\Controllers\Admin\Keuangan\NotifTagihanController::class, 'index'])->name('notif.index');
            Route::post('notif-tagihan/kirim', [\App\Http\Controllers\Admin\Keuangan\NotifTagihanController::class, 'kirimMassal'])->name('notif.kirim');
        });

        // ==========================================
        // WAKASEK KESISWAAN
        // ==========================================
        
        // Buku Induk Siswa
        Route::prefix('kesiswaan/buku-induk')->name('kesiswaan.buku_induk.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Kesiswaan\BukuIndukController::class, 'index'])->name('index');
            Route::get('/detail/{id}', [\App\Http\Controllers\Admin\Kesiswaan\BukuIndukController::class, 'detail'])->name('detail');
            Route::post('/simpan-detail', [\App\Http\Controllers\Admin\Kesiswaan\BukuIndukController::class, 'simpanDetail'])->name('simpan_detail');
            Route::get('/cetak-pdf/{id}', [\App\Http\Controllers\Admin\Kesiswaan\BukuIndukController::class, 'cetakPdf'])->name('cetak_pdf');
        });

        // Manajemen Rombel & Kenaikan Kelas
        Route::prefix('kesiswaan/rombel')->name('kesiswaan.rombel.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'index'])->name('index');
            Route::get('/atur/{id}', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'atur'])->name('atur');
            Route::post('/pindah', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'prosesPindah'])->name('pindah');
        });

        // Data Alumni
        Route::prefix('kesiswaan/alumni')->name('kesiswaan.alumni.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'alumni'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'simpanAlumni'])->name('simpan');
            Route::get('/template-import', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'templateImport'])->name('template_import');
            Route::post('/import', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'prosesImport'])->name('import');
            Route::get('/export', [\App\Http\Controllers\Admin\Kesiswaan\RombelController::class, 'exportAlumni'])->name('export');
        });
        
        // Tracer Study (Admin Kesiswaan)
        Route::prefix('kesiswaan/tracer')->name('kesiswaan.tracer.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Kesiswaan\TracerController::class, 'index'])->name('index');
            Route::post('/pertanyaan', [\App\Http\Controllers\Admin\Kesiswaan\TracerController::class, 'simpanPertanyaan'])->name('pertanyaan.simpan');
            Route::delete('/pertanyaan/{id}', [\App\Http\Controllers\Admin\Kesiswaan\TracerController::class, 'hapusPertanyaan'])->name('pertanyaan.hapus');
            Route::get('/detail/{id}', [\App\Http\Controllers\Admin\Kesiswaan\TracerController::class, 'detail'])->name('detail');
            Route::delete('/reset/{id}', [\App\Http\Controllers\Admin\Kesiswaan\TracerController::class, 'resetResponden'])->name('reset');
            Route::get('/export', [\App\Http\Controllers\Admin\Kesiswaan\TracerController::class, 'exportTracer'])->name('export');
        });
        
        // TUGAS PIKET (Kurikulum)
        Route::prefix('kurikulum/piket')->name('kurikulum.piket.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Kurikulum\PiketController::class, 'index'])->name('index');
            Route::get('/jadwal', [\App\Http\Controllers\Admin\Kurikulum\PiketController::class, 'jadwal'])->name('jadwal');
            Route::post('/jadwal', [\App\Http\Controllers\Admin\Kurikulum\PiketController::class, 'simpanJadwal'])->name('simpan_jadwal');
            Route::delete('/jadwal/{id}', [\App\Http\Controllers\Admin\Kurikulum\PiketController::class, 'hapusJadwal'])->name('hapus_jadwal');
        });
        
        // PPDB
        Route::get('ppdb/dashboard', [\App\Http\Controllers\Admin\Kesiswaan\PpdbController::class, 'dashboard'])->name('ppdb.dashboard');
        Route::get('ppdb', [\App\Http\Controllers\Admin\Kesiswaan\PpdbController::class, 'index'])->name('ppdb.index');
        Route::get('ppdb/{id}', [\App\Http\Controllers\Admin\Kesiswaan\PpdbController::class, 'show'])->name('ppdb.show');
        Route::post('ppdb/{id}/status', [\App\Http\Controllers\Admin\Kesiswaan\PpdbController::class, 'updateStatus'])->name('ppdb.updateStatus');
        Route::post('ppdb/{id}/migrate', [\App\Http\Controllers\Admin\Kesiswaan\PpdbController::class, 'migrateToSiswa'])->name('ppdb.migrateToSiswa');
        Route::delete('ppdb/{id}', [\App\Http\Controllers\Admin\Kesiswaan\PpdbController::class, 'destroy'])->name('ppdb.destroy');

        // ==========================================
        // WAKASEK HUMAS & HUBIN (WEBSITE & PUBLIKASI)
        // ==========================================
        Route::prefix('web')->name('web.')->group(function () {
            // Profil Web (Konfigurasi)
            Route::get('/profil', [\App\Http\Controllers\Admin\Web\WebProfilController::class, 'index'])->name('profil.index');
            Route::post('/profil', [\App\Http\Controllers\Admin\Web\WebProfilController::class, 'update'])->name('profil.update');
            
            // Slider
            Route::get('/slider', [\App\Http\Controllers\Admin\Web\SliderController::class, 'index'])->name('slider.index');
            Route::post('/slider', [\App\Http\Controllers\Admin\Web\SliderController::class, 'store'])->name('slider.store');
            Route::delete('/slider/{id}', [\App\Http\Controllers\Admin\Web\SliderController::class, 'destroy'])->name('slider.destroy');

            // Dudi / Mitra
            Route::get('/dudi', [\App\Http\Controllers\Admin\Web\DudiController::class, 'index'])->name('dudi.index');
            Route::post('/dudi', [\App\Http\Controllers\Admin\Web\DudiController::class, 'store'])->name('dudi.store');
            Route::delete('/dudi/{id}', [\App\Http\Controllers\Admin\Web\DudiController::class, 'destroy'])->name('dudi.destroy');

            // Berita
            Route::get('/berita', [\App\Http\Controllers\Admin\Web\BeritaController::class, 'index'])->name('berita.index');
            Route::get('/berita/create', [\App\Http\Controllers\Admin\Web\BeritaController::class, 'create'])->name('berita.create');
            Route::post('/berita', [\App\Http\Controllers\Admin\Web\BeritaController::class, 'store'])->name('berita.store');
            Route::get('/berita/{id}/edit', [\App\Http\Controllers\Admin\Web\BeritaController::class, 'edit'])->name('berita.edit');
            Route::post('/berita/{id}', [\App\Http\Controllers\Admin\Web\BeritaController::class, 'update'])->name('berita.update');
            Route::delete('/berita/{id}', [\App\Http\Controllers\Admin\Web\BeritaController::class, 'destroy'])->name('berita.destroy');

            // Galeri
            Route::get('/galeri', [\App\Http\Controllers\Admin\Web\GaleriController::class, 'index'])->name('galeri.index');
            Route::post('/galeri', [\App\Http\Controllers\Admin\Web\GaleriController::class, 'store'])->name('galeri.store');
            Route::delete('/galeri/{id}', [\App\Http\Controllers\Admin\Web\GaleriController::class, 'destroy'])->name('galeri.destroy');
        });

    }); // Close Route::prefix('admin')

    // App Setting and Role Group
    Route::post('/settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme');
    Route::resource('setting', SettingController::class);

    // D. Siswa Pembelajaran (KBM) & Keuangan
    Route::prefix('siswa')->name('siswa.')->group(function () {
        Route::get('materi', [\App\Http\Controllers\Siswa\MateriController::class, 'index'])->name('materi.index');
        Route::get('tugas', [\App\Http\Controllers\Siswa\TugasController::class, 'index'])->name('tugas.index');
        Route::post('tugas/upload', [\App\Http\Controllers\Siswa\TugasController::class, 'upload'])->name('tugas.upload');
        Route::get('ujian', [\App\Http\Controllers\Siswa\UjianController::class, 'index'])->name('ujian.index');
        
        // Tabungan
        Route::get('tabungan', [\App\Http\Controllers\Siswa\TabunganController::class, 'index'])->name('tabungan.index');
        Route::post('tabungan/transfer', [\App\Http\Controllers\Siswa\TabunganController::class, 'prosesTransfer'])->name('tabungan.transfer');
        Route::post('tabungan/bayar-tagihan', [\App\Http\Controllers\Siswa\TabunganController::class, 'prosesBayarTagihan'])->name('tabungan.bayar-tagihan');

        // Kedisiplinan
        Route::get('kedisiplinan', [\App\Http\Controllers\Siswa\KedisiplinanController::class, 'index'])->name('kedisiplinan.index');

        // Keuangan
        Route::get('keuangan', [\App\Http\Controllers\Siswa\KeuanganController::class, 'index'])->name('keuangan.index');
        Route::post('keuangan/bayar-qris', [\App\Http\Controllers\Siswa\KeuanganController::class, 'bayarQris'])->name('keuangan.bayar-qris');
    });

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

    // Siswa Rapor
    Route::get('/siswa/rapor', [\App\Http\Controllers\Siswa\RaporController::class, 'index'])->name('siswa.rapor.index');

    // Guru Piket Group
    Route::prefix('guru/piket')->name('guru.piket.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Guru\PiketController::class, 'dashboard'])->name('dashboard');
        Route::post('/tamu', [\App\Http\Controllers\Guru\PiketController::class, 'simpanBukuTamu'])->name('simpan_tamu');
        Route::post('/tamu/{id}/pulang', [\App\Http\Controllers\Guru\PiketController::class, 'updateJamPulangTamu'])->name('pulang_tamu');
        Route::delete('/tamu/{id}', [\App\Http\Controllers\Guru\PiketController::class, 'hapusBukuTamu'])->name('hapus_tamu');
        Route::post('/izin', [\App\Http\Controllers\Guru\PiketController::class, 'simpanIzinKeluar'])->name('simpan_izin');
        Route::delete('/izin/{id}', [\App\Http\Controllers\Guru\PiketController::class, 'hapusIzinKeluar'])->name('hapus_izin');
        Route::post('/jurnal', [\App\Http\Controllers\Guru\PiketController::class, 'simpanJurnal'])->name('simpan_jurnal');
        
        // Buku Tamu Digital Integration
        Route::get('/buku-tamu', [\App\Http\Controllers\Guru\PiketController::class, 'bukuTamu'])->name('buku-tamu.index');
        Route::post('/buku-tamu/ttd', [\App\Http\Controllers\Guru\PiketController::class, 'simpanTtd'])->name('buku-tamu.ttd');
        Route::get('/buku-tamu/{id}/cetak', [\App\Http\Controllers\Guru\PiketController::class, 'cetakThermal'])->name('buku-tamu.cetak');
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
    // Guru Disposisi (Kotak Disposisi Surat)
    Route::prefix('guru/disposisi')->name('guru.disposisi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\DisposisiController::class, 'index'])->name('index');
        Route::patch('/{id}/baca', [\App\Http\Controllers\Guru\DisposisiController::class, 'baca'])->name('baca');
        Route::post('/baca-semua', [\App\Http\Controllers\Guru\DisposisiController::class, 'bacaSemua'])->name('baca_semua');
    });
    // Guru Jadwal Mengajar Group
    Route::prefix('guru/jadwal-mengajar')->name('guru.jadwal-mengajar.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\JadwalMengajarController::class, 'index'])->name('index');
    });

    // Guru E-Learning (Materi, Tugas, Jurnal)
    Route::prefix('guru/elearning')->name('guru.elearning.')->group(function () {
        // Materi
        Route::prefix('materi')->name('materi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\MateriController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Guru\MateriController::class, 'store'])->name('store');
            Route::post('/{id}', [\App\Http\Controllers\Guru\MateriController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Guru\MateriController::class, 'destroy'])->name('destroy');
        });
        
        // Tugas
        Route::prefix('tugas')->name('tugas.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\TugasKbmController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Guru\TugasKbmController::class, 'store'])->name('store');
            Route::delete('/{id}', [\App\Http\Controllers\Guru\TugasKbmController::class, 'destroy'])->name('destroy');
            Route::get('/{tugas_id}/hasil', [\App\Http\Controllers\Guru\TugasKbmController::class, 'hasil'])->name('hasil');
            Route::post('/simpan-nilai', [\App\Http\Controllers\Guru\TugasKbmController::class, 'simpanNilai'])->name('simpan_nilai');
        });

        // Jurnal
        Route::prefix('jurnal')->name('jurnal.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'index'])->name('index');
            Route::get('/input', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'input'])->name('input');
            Route::post('/simpan', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'simpan'])->name('simpan');
            Route::get('/{id}/absen', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'absen'])->name('absen');
            Route::post('/simpan-absen', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'simpanAbsen'])->name('simpan_absen');
            Route::get('/{id}/detail', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'detailAbsen'])->name('detail');
            Route::delete('/{id}', [\App\Http\Controllers\Guru\JurnalKbmController::class, 'hapus'])->name('hapus');
        });

        // Kelas Virtual (Google Meet)
        Route::prefix('kelas-virtual')->name('kelas-virtual.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\KelasVirtualController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Guru\KelasVirtualController::class, 'store'])->name('store');
            Route::delete('/{id}', [\App\Http\Controllers\Guru\KelasVirtualController::class, 'destroy'])->name('destroy');
        });

        // Perpustakaan Digital (E-Book)
        Route::prefix('perpustakaan')->name('perpustakaan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\PerpustakaanController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Guru\PerpustakaanController::class, 'store'])->name('store');
            Route::delete('/{id}', [\App\Http\Controllers\Guru\PerpustakaanController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/counter', [\App\Http\Controllers\Guru\PerpustakaanController::class, 'counter'])->name('counter');
            Route::get('/{id}/baca', [\App\Http\Controllers\Guru\PerpustakaanController::class, 'baca'])->name('baca');
        });
    });

    // Guru Penilaian (eRapor) Group
    Route::prefix('guru/penilaian')->name('guru.penilaian.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\PenilaianController::class, 'index'])->name('index');
        
        Route::get('/tp', [\App\Http\Controllers\Guru\PenilaianController::class, 'tp'])->name('tp');
        Route::post('/tp/store', [\App\Http\Controllers\Guru\PenilaianController::class, 'storeTp'])->name('tp.store');
        Route::put('/tp/{id}', [\App\Http\Controllers\Guru\PenilaianController::class, 'updateTp'])->name('tp.update');
        Route::delete('/tp/{id}', [\App\Http\Controllers\Guru\PenilaianController::class, 'destroyTp'])->name('tp.destroy');
        
        Route::get('/formatif', [\App\Http\Controllers\Guru\PenilaianController::class, 'formatif'])->name('formatif');
        Route::post('/formatif/store', [\App\Http\Controllers\Guru\PenilaianController::class, 'storeFormatif'])->name('formatif.store');
        Route::get('/formatif/template', [\App\Http\Controllers\Guru\PenilaianController::class, 'templateFormatif'])->name('formatif.template');
        Route::post('/formatif/import', [\App\Http\Controllers\Guru\PenilaianController::class, 'importFormatif'])->name('formatif.import');

        Route::get('/sumatif', [\App\Http\Controllers\Guru\PenilaianController::class, 'sumatif'])->name('sumatif');
        Route::post('/sumatif/store', [\App\Http\Controllers\Guru\PenilaianController::class, 'storeSumatif'])->name('sumatif.store');
        Route::get('/sumatif/template', [\App\Http\Controllers\Guru\PenilaianController::class, 'templateSumatif'])->name('sumatif.template');
        Route::post('/sumatif/import', [\App\Http\Controllers\Guru\PenilaianController::class, 'importSumatif'])->name('sumatif.import');

        Route::get('/sikap-k13', [\App\Http\Controllers\Guru\PenilaianController::class, 'sikapK13'])->name('sikap_k13');
        Route::post('/sikap-k13/store', [\App\Http\Controllers\Guru\PenilaianController::class, 'storeSikapK13'])->name('sikap_k13.store');
        Route::get('/sikap-k13/template', [\App\Http\Controllers\Guru\PenilaianController::class, 'templateSikapK13'])->name('sikap_k13.template');
        Route::post('/sikap-k13/import', [\App\Http\Controllers\Guru\PenilaianController::class, 'importSikapK13'])->name('sikap_k13.import');
        
        Route::get('/generate-nilai-akhir', [\App\Http\Controllers\Guru\PenilaianController::class, 'halamanGenerateNilaiAkhir'])->name('halaman_generate_nilai_akhir');
        Route::post('/generate-nilai-akhir', [\App\Http\Controllers\Guru\PenilaianController::class, 'generateNilaiAkhir'])->name('store_generate_nilai');
        Route::get('/generate-nilai-akhir/download-excel', [\App\Http\Controllers\Guru\PenilaianController::class, 'downloadNilaiAkhirExcel'])->name('download_nilai_akhir_excel');
        Route::get('/generate-nilai-akhir/download-pdf', [\App\Http\Controllers\Guru\PenilaianController::class, 'downloadNilaiAkhirPdf'])->name('download_nilai_akhir_pdf');
    });

    // G. Guru PKL
    Route::prefix('guru/pkl')->name('guru.pkl.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\PklController::class, 'index'])->name('index');
        Route::post('/tp', [\App\Http\Controllers\Guru\PklController::class, 'storeTp'])->name('tp.store');
        
        Route::get('/monitoring', [\App\Http\Controllers\Guru\PklController::class, 'monitoring'])->name('monitoring');
        Route::get('/monitoring/cetak', [\App\Http\Controllers\Guru\PklController::class, 'cetakMonitoring'])->name('monitoring.cetak');
        Route::get('/jurnal', [\App\Http\Controllers\Guru\PklController::class, 'jurnal'])->name('jurnal');
        Route::post('/jurnal/validasi', [\App\Http\Controllers\Guru\PklController::class, 'jurnalValidasi'])->name('jurnal.validasi');
        Route::post('/laporan/validasi', [\App\Http\Controllers\Guru\PklController::class, 'laporanValidasi'])->name('laporan.validasi');
        
        Route::get('/kunjungan', [\App\Http\Controllers\Guru\PklController::class, 'kunjungan'])->name('kunjungan');
        Route::post('/kunjungan/simpan', [\App\Http\Controllers\Guru\PklController::class, 'kunjunganSimpan'])->name('kunjungan.simpan');
        
        Route::get('/nilai', [\App\Http\Controllers\Guru\PklController::class, 'nilai'])->name('nilai');
        Route::post('/nilai/simpan', [\App\Http\Controllers\Guru\PklController::class, 'simpanNilai'])->name('nilai.simpan');
        Route::get('/cetak-sertifikat/{id}', [\App\Http\Controllers\Guru\PklController::class, 'cetakSertifikat'])->name('cetak_sertifikat');
    });



    // H. Guru Kokurikuler
    Route::prefix('guru/kokurikuler')->name('guru.kokurikuler.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\KokurikulerController::class, 'index'])->name('index');
        Route::post('/nilai', [\App\Http\Controllers\Guru\KokurikulerController::class, 'storeNilai'])->name('nilai.store');
    });

    // Guru Wali Kelas Group
    Route::prefix('guru/walikelas')->name('guru.walikelas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\WaliKelasController::class, 'index'])->name('index');
        
        Route::get('/kehadiran', [\App\Http\Controllers\Guru\WaliKelasController::class, 'kehadiran'])->name('kehadiran');
        Route::post('/kehadiran/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeKehadiran'])->name('kehadiran.store');
        Route::get('/kehadiran/template', [\App\Http\Controllers\Guru\WaliKelasController::class, 'templateKehadiran'])->name('kehadiran.template');
        Route::post('/kehadiran/import', [\App\Http\Controllers\Guru\WaliKelasController::class, 'importKehadiran'])->name('kehadiran.import');
        
        Route::get('/catatan', [\App\Http\Controllers\Guru\WaliKelasController::class, 'catatan'])->name('catatan');
        Route::post('/catatan/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeCatatan'])->name('catatan.store');
        
        Route::get('/pkl', [\App\Http\Controllers\Guru\WaliKelasController::class, 'pkl'])->name('pkl');
        Route::post('/pkl/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storePkl'])->name('pkl.store');
        Route::post('/pkl/dudi/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeDudiPkl'])->name('pkl.dudi.store');

        Route::get('/pkl-k13', [\App\Http\Controllers\Guru\WaliKelasController::class, 'pklK13'])->name('pkl_k13');
        Route::post('/pkl-k13/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storePklK13'])->name('pkl_k13.store');

        Route::get('/deskripsi-p3', [\App\Http\Controllers\Guru\WaliKelasController::class, 'deskripsiP3'])->name('deskripsi_p3');
        Route::post('/deskripsi-p3/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeDeskripsiP3'])->name('deskripsi_p3.store');

        Route::get('/deskripsi-dpl', [\App\Http\Controllers\Guru\WaliKelasController::class, 'deskripsiDpl'])->name('deskripsi_dpl');
        Route::post('/deskripsi-dpl/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeDeskripsiDpl'])->name('deskripsi_dpl.store');

        Route::get('/kenaikan', [\App\Http\Controllers\Guru\WaliKelasController::class, 'kenaikan'])->name('kenaikan');
        Route::post('/kenaikan/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeKenaikan'])->name('kenaikan.store');
        
        Route::get('/data-siswa', [\App\Http\Controllers\Guru\WaliKelasController::class, 'dataSiswa'])->name('data_siswa');
        Route::post('/data-siswa/{id}', [\App\Http\Controllers\Guru\WaliKelasController::class, 'updateDataSiswa'])->name('data_siswa.update');

        Route::get('/nilai-siswa', [\App\Http\Controllers\Guru\WaliKelasController::class, 'nilaiSiswa'])->name('nilai_siswa');
        Route::post('/nilai-siswa/{id}', [\App\Http\Controllers\Guru\WaliKelasController::class, 'updateNilaiSiswa'])->name('nilai_siswa.update');

        Route::get('/ekskul', [\App\Http\Controllers\Guru\WaliKelasController::class, 'ekskul'])->name('ekskul');
        Route::post('/ekskul/store', [\App\Http\Controllers\Guru\WaliKelasController::class, 'storeEkskul'])->name('ekskul.store');

        Route::get('/leger', [\App\Http\Controllers\Guru\WaliKelasController::class, 'leger'])->name('leger');

        Route::get('/cetak-rapor', [\App\Http\Controllers\Guru\WaliKelasController::class, 'cetakRapor'])->name('cetak_rapor');
        Route::get('/skill-passport', [\App\Http\Controllers\Admin\UkkController::class, 'skillPassport'])->name('skill_passport');
        Route::get('/ukk', [\App\Http\Controllers\Admin\UkkController::class, 'index'])->name('ukk');
        Route::get('/buku-induk', [\App\Http\Controllers\Admin\CetakRaporController::class, 'bukuInduk'])->name('buku_induk');
        Route::get('/ijazah', [\App\Http\Controllers\Admin\IjazahController::class, 'index'])->name('ijazah');
    });

    // Guru P5 Group
    Route::prefix('guru/p5')->name('guru.p5.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guru\P5Controller::class, 'index'])->name('index');
        Route::get('/kelompok/{id}/input-nilai', [\App\Http\Controllers\Guru\P5Controller::class, 'inputNilai'])->name('input_nilai');
        Route::post('/kelompok/{id}/input-nilai', [\App\Http\Controllers\Guru\P5Controller::class, 'storeNilai'])->name('store_nilai');
    });



    // Rapor Printing Routes
    Route::get('/cetak/rapor-masal/{kelas_id}/cover', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakCoverMasal'])->name('cetak.rapor-masal.cover');
    Route::get('/cetak/rapor-masal/{kelas_id}/pelengkap', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakPelengkapMasal'])->name('cetak.rapor-masal.pelengkap');
    Route::get('/cetak/rapor-masal/{kelas_id}/nilai', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakNilaiMasal'])->name('cetak.rapor-masal.nilai');
    
    Route::get('/cetak/rapor/{id}/cover', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakCover'])->name('cetak.rapor.cover');
    Route::get('/cetak/rapor/{id}/nilai', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakNilai'])->name('cetak.rapor.nilai');
    Route::get('/cetak/rapor/{id}/p5', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakP5'])->name('cetak.rapor.p5');
    Route::get('/cetak/rapor/{id}/ukk', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakUkk'])->name('cetak.rapor.ukk');
    Route::get('/cetak/rapor/{id}/pelengkap', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakPelengkap'])->name('cetak.rapor.pelengkap');
    Route::get('/cetak/leger/{kelas_id}', [\App\Http\Controllers\Admin\CetakRaporController::class, 'cetakLeger'])->name('cetak.leger');
    Route::get('/cetak/leger-excel/{kelas_id}', [\App\Http\Controllers\Admin\CetakRaporController::class, 'exportLegerExcel'])->name('cetak.leger.excel');
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

Route::get('/dump-migrations', function () {
    $files = glob(database_path('migrations/2026_{05_28,05_29,06_*}*.php'), GLOB_BRACE);
    $output = "";
    foreach ($files as $file) {
        $output .= "\n\n--- " . basename($file) . " ---\n";
        $output .= file_get_contents($file);
    }
    return response($output)->header('Content-Type', 'text/plain');
});

Route::get('/jalankan-migrasi', function () {
    try {
        $migrations = [
            '2026_06_21_015044_create_tbl_jadwal_piket_table.php',
            '2026_06_21_015045_create_tbl_buku_tamu_table.php',
            '2026_06_21_015945_add_piket_fields_to_tbl_buku_tamu.php',
            '2026_06_21_090000_create_tbl_pkl_kelas_table.php',
            '2026_06_21_100000_alter_kategori_on_tbl_inventaris.php',
            '2026_06_21_100001_create_tbl_peminjaman_ruangan_table.php',
            '2026_06_21_100002_create_tbl_laporan_kerusakan_table.php',
            '2026_06_21_110000_create_tbl_konseling_table.php',
            '2026_06_21_120000_add_ttd_fields_to_tbl_buku_tamu.php',
            '2026_06_21_130000_add_is_read_to_tbl_disposisi_table.php',
            '2026_06_21_140000_create_tbl_kokurikuler_tables.php',
        ];

        $output = "";
        foreach ($migrations as $file) {
            $path = database_path('migrations/' . $file);
            if (file_exists($path)) {
                $migration = require_once $path;
                if (is_object($migration) && method_exists($migration, 'up')) {
                    try {
                        $migration->up();
                        $output .= "Berhasil run: $file <br>";
                    } catch (\Exception $e) {
                        $output .= "<span style='color:orange'>Skipped/Error $file: " . $e->getMessage() . "</span><br>";
                    }
                } else {
                    $output .= "Skipped (bukan class migration): $file <br>";
                }
            } else {
                $output .= "File tidak ditemukan: $file <br>";
            }
        }
        
        return response($output . " <br><b style='color:green'>Semua Migrasi Tambahan Selesai!</b>");
    } catch (\Exception $e) {
        return response("<b>Error:</b> " . $e->getMessage() . "<br><br>" . $e->getTraceAsString());
    }
});
Route::get('/fix-users', function () {
    \Illuminate\Support\Facades\DB::statement('UPDATE tbl_guru SET user_id = id_user WHERE id_user IS NOT NULL AND id_user > 0');
    \Illuminate\Support\Facades\DB::statement('UPDATE tbl_siswa SET user_id = id_user WHERE id_user IS NOT NULL AND id_user > 0');
    
    // Hapus sisa user duplikat (auto-heal) yang barusan terbuat (yang role aslinya belum lengkap)
    // agar database kembali bersih seperti sedia kala (opsional tapi disarankan)
    // Untuk amannya, kita biarkan saja. Yang penting user_id sudah kembali nyantol ke id_user lama.
    
    return 'Berhasil menyinkronkan data User ID lama (CI4) ke sistem baru! Silakan kembali ke menu Manajemen Guru di web, refresh halaman, dan role lama pasti sudah muncul semua!';
});