<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwal = \App\Models\JadwalUjian::find(41);
$jadwal->kelas_concat = "X IPS 1";
echo json_encode($jadwal);
