<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwalData = \Illuminate\Support\Facades\DB::table('tbl_jadwal_ujian as a')->first();

try {
    echo "ID is: " . $jadwalData['id'];
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
