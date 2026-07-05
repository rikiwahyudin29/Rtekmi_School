<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Auth
use App\Http\Controllers\Api\AuthApiController;
// Presensi
use App\Http\Controllers\Api\PresensiApiController;
use App\Http\Controllers\Api\PresensiGuruApiController;
// Ujian
use App\Http\Controllers\Api\UjianApiController;
use App\Http\Controllers\Api\CbtApiController;
// Tripay
use App\Http\Controllers\Api\TripayCallbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Auth
Route::post('login', [AuthApiController::class, 'login']);

// Rute-rute yang dilindungi API Key (Mobile Android)
Route::middleware('api.auth')->group(function () {
    // Presensi Guru (Sesuaikan dengan routing CI4 lama yang dipanggil Android)
    Route::prefix('presensi-guru')->group(function () {
        Route::get('status', [PresensiGuruApiController::class, 'statusAbsenHariIni']);
        Route::post('submit', [PresensiGuruApiController::class, 'submitAbsen']);
        Route::get('rekap', [PresensiGuruApiController::class, 'getRekap']);
        Route::post('izin', [PresensiGuruApiController::class, 'submitIzin']);
    });

    // Presensi Siswa (Sesuaikan dengan routing CI4 lama)
    Route::prefix('presensi')->group(function () {
        Route::post('submit', [PresensiApiController::class, 'submitAbsen']);
        Route::get('setting', [PresensiApiController::class, 'getSetting']);
        Route::get('summary', [PresensiApiController::class, 'getSummary']);
        Route::get('today-status', [PresensiApiController::class, 'getTodayStatus']);

        Route::get('riwayat', [PresensiApiController::class, 'getRiwayat']);
        Route::get('rekap', [PresensiApiController::class, 'getRekap']);
        Route::post('izin', [PresensiApiController::class, 'ajukanIzin']);
    });

    // Pengaturan
    Route::get('settings/location', [PresensiApiController::class, 'getLocationSetting']);

    // Akademik (Jadwal, Materi, Tugas, Raport, Ujian CBT)
    Route::prefix('akademik')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\AkademikApiController::class, 'getDashboardSummary']);
        Route::get('materi', [\App\Http\Controllers\Api\AkademikApiController::class, 'getMateri']);
        Route::get('tugas', [\App\Http\Controllers\Api\AkademikApiController::class, 'getTugas']);
        Route::post('submit_tugas', [\App\Http\Controllers\Api\AkademikApiController::class, 'submitTugas']);
        Route::get('raport', [\App\Http\Controllers\Api\AkademikApiController::class, 'getRaportMerdeka']);
        Route::get('raport_lama', [\App\Http\Controllers\Api\AkademikApiController::class, 'getRaport']);
    });

    // Modul Keuangan
    Route::prefix('keuangan')->group(function () {
        Route::get('list', [\App\Http\Controllers\Api\KeuanganApiController::class, 'getTagihan']);
        Route::get('tagihan', [\App\Http\Controllers\Api\KeuanganApiController::class, 'getTagihan']);
        Route::post('bayar', [\App\Http\Controllers\Api\KeuanganApiController::class, 'bayarTripay']);
        Route::post('bayarTripay', [\App\Http\Controllers\Api\KeuanganApiController::class, 'bayarTripay']);
    });

    // Ujian Siswa (Sesuaikan dengan routing CI4 lama)
    Route::prefix('ujian')->group(function () {
        Route::get('jadwal', [UjianApiController::class, 'getJadwal']);
        Route::post('download', [UjianApiController::class, 'downloadSoal']);
        Route::post('submit', [UjianApiController::class, 'submitJawaban']);
        Route::get('cek_waktu', [UjianApiController::class, 'cekWaktu']);
    });

    // CBT
    Route::prefix('cbt')->group(function () {
        Route::get('soal/{ujian_id}', [CbtApiController::class, 'getSoal']);
        Route::post('jawaban', [CbtApiController::class, 'saveJawaban']);
        Route::post('finish/{ujian_id}', [CbtApiController::class, 'finish']);
    });
});

// Tripay Callback
Route::post('tripay/callback', [TripayCallbackController::class, 'handle']);

// IoT Scanner Device (Sesuaikan dengan routing CI4 lama)
Route::post('iot/scan', [\App\Http\Controllers\Admin\PresensiController::class, 'prosesScan']);

// Endpoint Sementara (Jangan Dihapus)
Route::get('/run-migrate-temp-3', function() {
    try {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_06_21_110000_create_tbl_konseling_table.php',
            '--force' => true
        ]);
        return "Migration Konseling executed successfully: <br>" . nl2br(Artisan::output());
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
