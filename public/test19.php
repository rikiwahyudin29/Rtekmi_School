<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwal = \App\Models\JadwalUjian::first();
$jadwal->total_siswa = 100;
$jadwal->is_generated = true;
$jadwal->kelas_concat = "TEST";

echo json_encode($jadwal->toArray());
