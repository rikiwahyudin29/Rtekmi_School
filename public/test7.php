<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwal = \App\Models\JadwalUjian::first();
if ($jadwal) {
    $jadwal->kelas_concat = "X IPS 1, X IPS 2";
    $jadwal->total_siswa = 100;
    
    $json = $jadwal->toJson();
    echo "JSON OUTPUT: \n" . $json . "\n";
    
    $decoded = json_decode($json, true);
    if (isset($decoded['kelas_concat'])) {
        echo "kelas_concat is present: " . $decoded['kelas_concat'] . "\n";
    } else {
        echo "kelas_concat IS MISSING!\n";
    }
} else {
    echo "No JadwalUjian found.\n";
}
