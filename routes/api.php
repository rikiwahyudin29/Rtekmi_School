<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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
