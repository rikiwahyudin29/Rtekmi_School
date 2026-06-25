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
    Route::get('riwayat', [PresensiApiController::class, 'getRiwayat']);
    Route::get('rekap', [PresensiApiController::class, 'getRekap']);
    Route::post('ajukan_izin', [PresensiApiController::class, 'ajukanIzin']);
});

// Ujian Siswa
Route::prefix('ujian')->group(function () {
    Route::post('jadwal', [UjianApiController::class, 'getJadwal']);
    Route::post('download', [UjianApiController::class, 'downloadSoal']);
    Route::post('submit', [UjianApiController::class, 'submitJawaban']);
    Route::post('waktu', [UjianApiController::class, 'cekWaktu']);
});

// CBT
Route::prefix('cbt')->group(function () {
    Route::get('soal/{ujian_id}', [CbtApiController::class, 'getSoal']);
    Route::post('jawaban', [CbtApiController::class, 'saveJawaban']);
    Route::post('finish/{ujian_id}', [CbtApiController::class, 'finish']);
});

// Tripay Callback
Route::post('tripay/callback', [TripayCallbackController::class, 'handle']);

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
