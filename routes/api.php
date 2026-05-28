<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth Login API
Route::post('login', [\App\Http\Controllers\Api\AuthApiController::class, 'login']);

// PRESENSI API (SISWA)
Route::post('presensi/submit', [\App\Http\Controllers\Api\PresensiApiController::class, 'submitAbsen']);
Route::get('presensi/setting', [\App\Http\Controllers\Api\PresensiApiController::class, 'getSetting']);
Route::get('presensi/riwayat', [\App\Http\Controllers\Api\PresensiApiController::class, 'getRiwayat']);
Route::get('presensi/rekap', [\App\Http\Controllers\Api\PresensiApiController::class, 'getRekap']);
Route::post('presensi/ajukan_izin', [\App\Http\Controllers\Api\PresensiApiController::class, 'ajukanIzin']);

// PRESENSI GURU API
Route::get('presensi-guru/status', [\App\Http\Controllers\Api\PresensiGuruApiController::class, 'statusAbsenHariIni']);
Route::post('presensi-guru/submit', [\App\Http\Controllers\Api\PresensiGuruApiController::class, 'submitAbsen']);
Route::get('presensi-guru/rekap', [\App\Http\Controllers\Api\PresensiGuruApiController::class, 'getRekap']);
Route::post('presensi-guru/izin', [\App\Http\Controllers\Api\PresensiGuruApiController::class, 'submitIzin']);

// Ujian API
Route::prefix('ujian')->name('ujian.')->group(function () {
    Route::get('jadwal', [\App\Http\Controllers\Api\UjianApiController::class, 'getJadwal']);
    Route::post('download', [\App\Http\Controllers\Api\UjianApiController::class, 'downloadSoal']);
    Route::post('submit', [\App\Http\Controllers\Api\UjianApiController::class, 'submitJawaban']);
    Route::get('cek_waktu', [\App\Http\Controllers\Api\UjianApiController::class, 'cekWaktu']);
});
